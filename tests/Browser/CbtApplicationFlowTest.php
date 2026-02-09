<?php

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

uses(DatabaseMigrations::class);

function seedE2eScenario(): array
{
    $admin = User::updateOrCreate(
        ['email' => 'admin@smait-baitulmuslim.sch.id'],
        ['name' => 'Administrator', 'password' => Hash::make('password'), 'role' => 'admin']
    );

    $subject = Subject::create(['name' => 'Matematika QA', 'code' => 'MTHQA']);
    $classroom = Classroom::create(['name' => 'X QA 1', 'level' => 'X']);

    $teacherUser = User::create([
        'name' => 'Teacher E2E',
        'email' => 'teacher.e2e@example.test',
        'password' => Hash::make('password'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id, 'nip' => '199900000001']);
    $teacher->subjects()->sync([$subject->id]);

    $studentUser = User::create([
        'name' => 'Student E2E',
        'email' => 'student.e2e@example.test',
        'password' => Hash::make('20261234'),
        'role' => 'student',
    ]);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'nis' => '20261234',
        'classroom_id' => $classroom->id,
    ]);

    // Build one question group used by exam form step 2.
    foreach (range(1, 4) as $i) {
        $question = Question::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'title' => 'E2E Group',
            'type' => 'multiple_choice',
            'text' => "<p>Question MC {$i}</p>",
            'explanation' => 'Explanation',
            'score' => 25,
        ]);

        $correctLabel = 'A';
        foreach (['A', 'B', 'C', 'D'] as $label) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'text' => "Option {$label}",
                'is_correct' => $label === $correctLabel,
            ]);
        }
    }

    Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'E2E Group',
        'type' => 'essay',
        'text' => '<p>Essay question E2E</p>',
        'answer_key' => 'Kunci essay',
        'explanation' => 'Essay explanation',
        'score' => 25,
    ]);

    return compact('admin', 'teacherUser', 'teacher', 'studentUser', 'student', 'subject', 'classroom');
}

test('admin teacher setup, resilient student journey, grading close loop', function () {
    $scenario = seedE2eScenario();

    $this->browse(function (Browser $browser) use ($scenario) {
        $browser->visit('/login')
            ->type('#email', $scenario['admin']->email)
            ->type('#password', 'password')
            ->press('MASUK BERANDA')
            ->waitForLocation('/admin/dashboard')
            ->visit('/admin/teachers')
            ->assertSee('DATA PENGAJAR')
            ->waitForLivewire(fn (Browser $b) => $b->click("button[wire\\:click='openAddModal']"))
            ->waitFor("input[wire\\:model='teacherForm.name']")
            ->type("input[wire\\:model='teacherForm.name']", 'QA Teacher Livewire')
            ->type("input[wire\\:model='teacherForm.email']", 'qa.teacher.livewire@example.test')
            ->type("input[wire\\:model='teacherForm.password']", 'password123')
            ->runScript("Array.from(document.querySelectorAll('label')).find(el => (el.innerText || '').includes('{$scenario['subject']->name}'))?.click();")
            ->waitForLivewire(fn (Browser $b) => $b->click("button[wire\\:click='saveTeacher']"))
            ->waitForText('QA TEACHER LIVEWIRE')
            ->assertSee('qa.teacher.livewire@example.test');
    });

    $createdTeacher = Teacher::query()
        ->whereHas('user', fn ($q) => $q->where('email', 'qa.teacher.livewire@example.test'))
        ->firstOrFail();

    $this->browse(function (Browser $browser) use ($createdTeacher) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/login')
            ->type('#email', 'admin@smait-baitulmuslim.sch.id')
            ->type('#password', 'password')
            ->press('MASUK BERANDA')
            ->waitForLocation('/admin/dashboard')
            ->visit('/admin/teachers')
            ->waitForLivewireToLoad()
            ->waitForLivewire(fn (Browser $b) => $b->click("button[wire\\:click='openEditModal({$createdTeacher->id})']"));

        $browser->waitFor("input[wire\\:model='teacherForm.name']")
            ->clear("input[wire\\:model='teacherForm.name']")
            ->type("input[wire\\:model='teacherForm.name']", 'QA Teacher Updated')
            ->waitForLivewire(fn (Browser $b) => $b->click("button[wire\\:click='saveTeacher']"))
            ->waitForText('QA TEACHER UPDATED')
            ->assertSee('QA TEACHER UPDATED')
            ->waitForLivewire(fn (Browser $b) => $b->click("button[wire\\:click='openDeleteModal({$createdTeacher->id})']"));

        $browser->waitForText('Hapus Data Guru?')
            ->waitForLivewire(fn (Browser $b) => $b->click("button[wire\\:click='deleteTeacher']"))
            ->pause(500)
            ->assertDontSee('qa.teacher.livewire@example.test');
    });

    $this->browse(function (Browser $browser) use ($scenario) {
        $browser->driver->manage()->deleteAllCookies();

        $date = now()->toDateString();
        $start = now()->subMinutes(10)->format('H:i');
        $end = now()->addMinutes(100)->format('H:i');

        $browser->visit('/teacher/login')
            ->type('#email', $scenario['teacherUser']->email)
            ->type('#password', 'password')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/teacher/dashboard')
            ->visit('/teacher/exams/create')
            ->waitForLivewireToLoad()
            ->type("input[wire\\:model='name']", 'E2E Published Exam')
            ->select("select[wire\\:model='subject_id']", (string) $scenario['subject']->id)
            ->runScript("
                const dateInput = document.querySelector(\"input[type='date']\");
                if (dateInput) {
                    dateInput.value = '{$date}';
                    dateInput.dispatchEvent(new Event('input', { bubbles: true }));
                    dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                const times = document.querySelectorAll(\"input[type='time']\");
                if (times[0]) {
                    times[0].value = '{$start}';
                    times[0].dispatchEvent(new Event('input', { bubbles: true }));
                    times[0].dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (times[1]) {
                    times[1].value = '{$end}';
                    times[1].dispatchEvent(new Event('input', { bubbles: true }));
                    times[1].dispatchEvent(new Event('change', { bubbles: true }));
                }

                const tokenInput = document.querySelector(\"input[wire\\\\:model='token']\");
                if (tokenInput) {
                    tokenInput.value = 'E2E123';
                    tokenInput.dispatchEvent(new Event('input', { bubbles: true }));
                    tokenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            ");

        $browser->runScript("Array.from(document.querySelectorAll('label')).find(el => (el.innerText || '').includes('{$scenario['classroom']->name}'))?.click();")
            ->click("button[wire\\:click='nextStep']")
            ->waitFor("div[wire\\:click^='toggleQuestionGroup']")
            ->runScript("document.querySelector(\"div[wire\\\\:click^='toggleQuestionGroup']\")?.click();")
            ->click("button[type='submit']")
            ->waitUsing(15, 100, fn () => str_contains($browser->driver->getCurrentURL(), '/teacher/exams') || str_contains($browser->driver->getCurrentURL(), '/teacher/login'));

        if (str_contains($browser->driver->getCurrentURL(), '/teacher/login')) {
            $browser->type('#email', $scenario['teacherUser']->email)
                ->type('#password', 'password')
                ->press('MASUK DASHBOARD')
                ->waitForLocation('/teacher/dashboard')
                ->visit('/teacher/exams');
        }

        $browser->assertSee('E2E Published Exam');
    });

    $exam = Exam::query()->where('name', 'E2E Published Exam')->firstOrFail();

    $this->browse(function (Browser $browser) use ($scenario, $exam) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/student/login')
            ->type('#email', $scenario['studentUser']->email)
            ->type('#password', '20261234')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/student/dashboard')
            ->visit("/student/exam/{$exam->id}/start")
            ->waitFor("input[wire\\:model\\.live='token']")
            ->type("input[wire\\:model\\.live='token']", 'E2E123')
            ->waitForLivewire(fn (Browser $b) => $b->press('Mulai Ujian'))
            ->waitForLocation("/student/exam/{$exam->id}/take")
            ->press('MULAI MENGERJAKAN SEKARANG')
            ->pause(1500);

        $timeBefore = (int) $browser->script("return Alpine.store('exam').timeLeft;")[0];
        $browser->pause(1500);
        $timeAfter = (int) $browser->script("return Alpine.store('exam').timeLeft;")[0];
        expect($timeAfter)->toBeLessThan($timeBefore);

        $browser->pause(16000);

        // Fail save-answer requests and put answer into pending queue.
        $browser->script([
            "window.__originalFetch = window.fetch;",
            "window.fetch = (url, opts) => url.includes('/save-answer') ? Promise.resolve(new Response(JSON.stringify({ success: false, message: 'Mock save failed' }), { status: 500, headers: { 'Content-Type': 'application/json' } })) : window.__originalFetch(url, opts);",
        ]);

        $browser->waitFor("input[type='radio']")
            ->click("input[type='radio']")
            ->pause(1200)
            ->script("window.dispatchEvent(new Event('offline'));");

        $browser->waitForText('Koneksi internet terputus');
        $pendingCount = (int) $browser->script(
            "const el = document.querySelector('[x-data=\"examData()\"]'); return el ? Object.keys(Alpine.\$data(el).pendingSaves).length : 0;"
        )[0];
        expect($pendingCount)->toBeGreaterThan(0);

        // Recover connection and auto-sync pending saves.
        $browser->script([
            "window.fetch = window.__originalFetch;",
            "window.dispatchEvent(new Event('online'));",
        ])->pause(7000);

        $pendingAfter = (int) $browser->script(
            "const el = document.querySelector('[x-data=\"examData()\"]'); return el ? Object.keys(Alpine.\$data(el).pendingSaves).length : 0;"
        )[0];
        expect($pendingAfter)->toBe(0);

        $browser->press('Kumpulkan Jawaban')
            ->waitForText('Konfirmasi Selesai Ujian')
            ->press('Ya, Selesaikan Sekarang')
            ->waitUsing(10, 100, fn () => str_contains($browser->driver->getCurrentURL(), '/student/results/'));
    });

    $attempt = ExamAttempt::query()
        ->where('exam_id', $exam->id)
        ->where('student_id', $scenario['student']->id)
        ->firstOrFail();
    expect($attempt->last_seen_at)->not->toBeNull();

    $this->browse(function (Browser $browser) use ($scenario, $exam) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/teacher/login')
            ->type('#email', $scenario['teacherUser']->email)
            ->type('#password', 'password')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/teacher/dashboard')
            ->visit("/teacher/grading/{$exam->id}/{$scenario['student']->id}")
            ->waitFor("input[type='number']")
            ->clear("input[type='number']")
            ->type("input[type='number']", '25')
            ->waitForLivewire(fn (Browser $b) => $b->press('SIMPAN & SELESAI'))
            ->waitForLocation("/teacher/grading/{$exam->id}");
    });

    $attempt->refresh();
    expect((string) $attempt->status->value)->toBe('graded');

    $this->browse(function (Browser $browser) use ($scenario, $attempt) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/student/login')
            ->type('#email', $scenario['studentUser']->email)
            ->type('#password', '20261234')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/student/dashboard')
            ->visit("/student/results/{$attempt->id}")
            ->assertSee('Nilai Akhir')
            ->assertSee((string) number_format((float) $attempt->percentage, 1, '.', ''));
    });
});
