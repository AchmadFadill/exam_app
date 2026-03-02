import http from 'k6/http';
import { check, fail, sleep } from 'k6';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

const BASE_URL = (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const TEST_TYPE = (__ENV.TEST_TYPE || 'smoke').toLowerCase();
const EXAM_TOKEN = __ENV.EXAM_TOKEN || '';
const LOGIN_PATH = __ENV.LOGIN_PATH || '/student/login';

// Prefer EXAM_ID when given; otherwise pick randomly from EXAM_IDS (e.g. "11,12,13,14,15").
const EXAM_IDS = (__ENV.EXAM_ID
    ? [String(__ENV.EXAM_ID)]
    : String(__ENV.EXAM_IDS || '1,2,3,4,5')
        .split(',')
        .map((v) => v.trim())
        .filter(Boolean)
);

function buildStages(type) {
    switch (type) {
        case 'load':
            return [
                { duration: '2m', target: 400 },
                { duration: '10m', target: 400 },
                { duration: '1m', target: 0 },
            ];
        case 'stress':
            return [
                { duration: '5m', target: 1000 },
                { duration: '2m', target: 1000 },
                { duration: '1m', target: 0 },
            ];
        case 'spike':
            return [
                { duration: '30s', target: 500 },
                { duration: '2m', target: 500 },
                { duration: '30s', target: 0 },
            ];
        case 'smoke':
        default:
            return [
                { duration: '1m', target: 1 },
                { duration: '10s', target: 0 },
            ];
    }
}

export const options = {
    discardResponseBodies: false,
    stages: buildStages(TEST_TYPE),
    thresholds: {
        http_req_failed: ['rate<0.05'],
        http_req_duration: ['p(95)<3000'],
    },
};

function htmlDecode(input) {
    return String(input || '')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>');
}

function extractCsrf(html) {
    const fromInput = String(html).match(/name="_token"\s+value="([^"]+)"/i);
    if (fromInput) return fromInput[1];

    const fromMeta = String(html).match(/meta\s+name="csrf-token"\s+content="([^"]+)"/i);
    if (fromMeta) return fromMeta[1];

    const fromScript = String(html).match(/'X-CSRF-TOKEN'\s*:\s*'([^']+)'/i);
    if (fromScript) return fromScript[1];

    return null;
}

function extractLivewireComponent(html) {
    const snapshotMatch = String(html).match(/wire:snapshot="([^"]+)"/i);
    const idMatch = String(html).match(/wire:id="([^"]+)"/i);

    if (!snapshotMatch || !idMatch) {
        return null;
    }

    return {
        snapshot: htmlDecode(snapshotMatch[1]),
        id: htmlDecode(idMatch[1]),
    };
}

function pickExamId() {
    const idx = randomIntBetween(0, EXAM_IDS.length - 1);
    return EXAM_IDS[idx];
}

function jsonHeaders(csrf) {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json, text/plain, */*',
    };
}

function postJson(path, payload, csrf, tags = {}) {
    return http.post(`${BASE_URL}${path}`, JSON.stringify(payload), {
        headers: jsonHeaders(csrf),
        tags,
    });
}

function loginStudent(vuNumber) {
    const loginPage = http.get(`${BASE_URL}${LOGIN_PATH}`, {
        tags: { name: 'student_login_page' },
    });

    check(loginPage, {
        'login page loaded': (r) => r.status === 200,
    });

    const csrf = extractCsrf(loginPage.body);
    if (!csrf) {
        fail(`CSRF token not found at ${LOGIN_PATH}`);
    }

    const email = `student${vuNumber}@nexa.local`;
    const payload = {
        _token: csrf,
        email,
        password: __ENV.STUDENT_PASSWORD || 'password123',
    };

    const loginRes = http.post(`${BASE_URL}${LOGIN_PATH}`, payload, {
        redirects: 5,
        tags: { name: 'student_login_submit' },
    });

    check(loginRes, {
        'login submit accepted': (r) => r.status === 200 || r.status === 302,
    });

    const dashboard = http.get(`${BASE_URL}/student/dashboard`, {
        tags: { name: 'student_dashboard' },
    });

    const ok = check(dashboard, {
        'dashboard loaded after login': (r) => r.status === 200,
    });

    if (!ok) {
        fail(`Login failed for ${email}. Check staging users and credentials.`);
    }

    return { csrf: extractCsrf(dashboard.body) || csrf, email };
}

function startExam(examId, examToken) {
    const startPage = http.get(`${BASE_URL}/student/exam/${examId}/start`, {
        tags: { name: 'exam_start_page' },
    });

    const pageOk = check(startPage, {
        'exam start page loaded': (r) => r.status === 200,
    });

    if (!pageOk) {
        fail(`Exam start page failed for exam ${examId}`);
    }

    const csrf = extractCsrf(startPage.body);
    if (!csrf) {
        fail(`CSRF token not found on exam start page for exam ${examId}`);
    }

    const component = extractLivewireComponent(startPage.body);
    if (!component) {
        fail(`Livewire component snapshot not found on exam start page for exam ${examId}`);
    }

    const updatePayload = {
        _token: csrf,
        components: [
            {
                snapshot: component.snapshot,
                updates: examToken ? { token: examToken } : {},
                calls: [
                    {
                        path: '',
                        method: 'startExam',
                        params: [],
                    },
                ],
            },
        ],
    };

    const startRes = http.post(`${BASE_URL}/livewire/update`, JSON.stringify(updatePayload), {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Livewire': 'true',
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json, text/plain, */*',
        },
        tags: { name: 'exam_start_submit_livewire' },
    });

    const startOk = check(startRes, {
        'exam start livewire request ok': (r) => r.status >= 200 && r.status < 300,
    });

    if (!startOk) {
        fail(`Livewire start exam failed (${startRes.status}) for exam ${examId}`);
    }

    const takePage = http.get(`${BASE_URL}/student/exam/${examId}/take`, {
        tags: { name: 'exam_take_page' },
    });

    const takeOk = check(takePage, {
        'exam take page loaded': (r) => r.status === 200,
    });

    if (!takeOk) {
        fail(`Failed to open exam take page for exam ${examId}. Check token/time window.`);
    }

    return {
        examHtml: takePage.body,
        csrf: extractCsrf(takePage.body) || csrf,
    };
}

function extractQuestionsFromTakePage(html) {
    const source = String(html);

    const match = source.match(/questions:\s*(\[[\s\S]*?\])\s*,\s*violations:/);
    if (!match) {
        fail('Failed to parse questions payload from exam page HTML');
    }

    try {
        const questions = JSON.parse(match[1]);
        if (!Array.isArray(questions) || questions.length === 0) {
            fail('Parsed questions payload is empty');
        }
        return questions;
    } catch (error) {
        fail(`Questions payload parse error: ${error.message}`);
    }
}

function randomEssayAnswer() {
    return `k6-answer-${__VU}-${Date.now()}`;
}

function pickRandomAnswer(question) {
    if (question.type === 'essay') {
        return randomEssayAnswer();
    }

    const options = Array.isArray(question.options) ? question.options : [];
    if (options.length === 0) return null;

    const idx = randomIntBetween(0, options.length - 1);
    return options[idx].id;
}

function sendHeartbeat(examId, csrf) {
    return postJson(`/student/exam/${examId}/heartbeat`, {}, csrf, { name: 'exam_heartbeat' });
}

function saveAnswer(examId, csrf, questionId, answer) {
    return postJson(
        `/student/exam/${examId}/save-answer`,
        { question_id: questionId, answer },
        csrf,
        { name: 'exam_save_answer' }
    );
}

function logViolation(examId, csrf, count = 1) {
    return postJson(
        `/student/exam/${examId}/log-violation`,
        {
            type: 'tab_switch',
            message: 'k6 simulated tab switch violation',
            count,
        },
        csrf,
        { name: 'exam_log_violation' }
    );
}

function submitExam(examId, csrf, answers) {
    return postJson(`/student/exam/${examId}/submit`, { answers }, csrf, { name: 'exam_submit' });
}

export default function () {
    const examId = pickExamId();

    loginStudent(__VU);

    const started = startExam(examId, EXAM_TOKEN);
    const csrf = started.csrf;
    const questions = extractQuestionsFromTakePage(started.examHtml);

    const finalAnswers = {};
    let lastHeartbeatAt = Date.now();
    const simulateViolation = Math.random() < 0.05;
    let violationSent = false;

    for (let i = 0; i < 20; i++) {
        const question = questions[randomIntBetween(0, questions.length - 1)];
        const answer = pickRandomAnswer(question);

        if (answer !== null && answer !== undefined) {
            finalAnswers[String(question.id)] = answer;

            const saveRes = saveAnswer(examId, csrf, question.id, answer);
            check(saveRes, {
                'save-answer success': (r) => r.status === 200 && String(r.body).includes('"success":true'),
            });
        }

        if (!violationSent && simulateViolation && i >= randomIntBetween(3, 16)) {
            const violationRes = logViolation(examId, csrf, 1);
            check(violationRes, {
                'violation endpoint reachable': (r) => r.status === 200,
            });
            violationSent = true;
        }

        const now = Date.now();
        if (now - lastHeartbeatAt >= 30000) {
            const heartbeatRes = sendHeartbeat(examId, csrf);
            check(heartbeatRes, {
                'heartbeat success': (r) => r.status === 200,
            });
            lastHeartbeatAt = now;
        }

        sleep(randomIntBetween(10, 20));
    }

    const heartbeatRes = sendHeartbeat(examId, csrf);
    check(heartbeatRes, {
        'final heartbeat success': (r) => r.status === 200,
    });

    const submitRes = submitExam(examId, csrf, finalAnswers);

    check(submitRes, {
        'submit request success': (r) => r.status === 200,
        'submit returns success true': (r) => String(r.body).includes('"success":true'),
    });
}
