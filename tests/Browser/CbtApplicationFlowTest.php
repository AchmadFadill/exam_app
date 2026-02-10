<?php

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\StudentAnswer;
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
            ->waitFor('input[name="email"]', 15)
            ->waitFor('input[name="password"]', 15)
            ->type('input[name="email"]', $scenario['admin']->email)
            ->type('input[name="password"]', 'password')
            ->press('MASUK BERANDA')
            ->waitForLocation('/admin/dashboard')
            ->pause(1000)
            ->screenshot('admin_dashboard')
            ->pause(1000)
            ->visit('/admin/teachers')
            ->pause(1000)
            ->assertSee('DATA PENGAJAR');
    });

    $createdUser = User::create([
        'name' => 'QA Teacher Livewire',
        'email' => 'qa.teacher.livewire@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $createdTeacher = Teacher::create([
        'user_id' => $createdUser->id,
        'nip' => '199900000099',
    ]);

    $createdUser->update(['name' => 'QA Teacher Updated']);
    expect($createdUser->fresh()->name)->toBe('QA Teacher Updated');

    $createdTeacher->delete();
    $createdUser->delete();

    expect(
        Teacher::query()
            ->whereHas('user', fn ($q) => $q->where('email', 'qa.teacher.livewire@example.test'))
            ->exists()
    )->toBeFalse();

    $this->browse(function (Browser $browser) use ($scenario) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/teacher/login')
            ->waitFor('input[name="email"]', 15)
            ->waitFor('input[name="password"]', 15)
            ->type('input[name="email"]', $scenario['teacherUser']->email)
            ->type('input[name="password"]', 'password')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/teacher/dashboard')
            ->pause(1000)
            ->visit('/teacher/exams/create')
            ->waitForLivewireToLoad()
            ->pause(1000)
            ->assertSee('JADWALKAN');
    });

    $exam = Exam::create([
        'teacher_id' => $scenario['teacher']->id,
        'subject_id' => $scenario['subject']->id,
        'name' => 'E2E Published Exam',
        'date' => now()->toDateString(),
        'start_time' => now()->subMinutes(10)->format('H:i:s'),
        'end_time' => now()->addMinutes(100)->format('H:i:s'),
        'duration_minutes' => 90,
        'token' => 'E2E123',
        'passing_grade' => 70,
        'default_score' => 20,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);
    $exam->classrooms()->sync([$scenario['classroom']->id]);

    $questions = Question::query()
        ->where('teacher_id', $scenario['teacher']->id)
        ->where('title', 'E2E Group')
        ->orderBy('id')
        ->get();

    $pivot = [];
    foreach ($questions as $index => $question) {
        $pivot[$question->id] = [
            'order' => $index + 1,
            'score' => 20,
        ];
    }
    $exam->questions()->sync($pivot);

    $this->browse(function (Browser $browser) use ($scenario, $exam) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/student/login')
            ->waitFor('input[name="email"]', 15)
            ->waitFor('input[name="password"]', 15)
            ->type('input[name="email"]', $scenario['studentUser']->email)
            ->type('input[name="password"]', '20261234')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/student/dashboard', 15)
            ->pause(1000)
            ->visit("/student/exam/{$exam->id}/start")
            ->waitForLivewireToLoad()
            ->pause(1000);

        $browser->runScript("
            const c = window.Livewire.all().find(x => x.name === 'student.exam-start');
            if (!c) throw new Error('student.exam-start component not found');
            c.\$wire.set('token', 'E2E123');
            c.\$wire.call('startExam');
        ");

        $browser
            ->waitForLocation("/student/exam/{$exam->id}/take", 15)
            ->press('MULAI MENGERJAKAN SEKARANG')
            ->pause(1000)
            ->screenshot('exam_start')
            ->pause(1000);

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
        $browser->screenshot('offline_mode')->pause(1000);
        $pendingCount = (int) $browser->script(
            "const el = document.querySelector('[x-data=\"examData()\"]'); return el ? Object.keys(Alpine.\$data(el).pendingSaves).length : 0;"
        )[0];
        expect($pendingCount)->toBeGreaterThan(0);

        // Recover connection and auto-sync pending saves.
        $browser->runScript("window.fetch = window.__originalFetch; window.dispatchEvent(new Event('online'));")
            ->pause(7000);

        $pendingAfter = (int) $browser->script(
            "const el = document.querySelector('[x-data=\"examData()\"]'); return el ? Object.keys(Alpine.\$data(el).pendingSaves).length : 0;"
        )[0];
        expect($pendingAfter)->toBe(0);

        $browser->press('Kumpulkan Jawaban')
            ->waitForText('Konfirmasi Selesai Ujian')
            ->pause(1000)
            ->press('Ya, Selesaikan Sekarang')
            ->pause(1000)
            ->waitUsing(15, 100, fn () => str_contains($browser->driver->getCurrentURL(), '/student/results/'));
    });

    $attempt = ExamAttempt::query()
        ->where('exam_id', $exam->id)
        ->where('student_id', $scenario['student']->id)
        ->firstOrFail();
    expect($attempt->last_seen_at)->not->toBeNull();

    $essayQuestion = $exam->questions()->where('questions.type', 'essay')->first();
    if ($essayQuestion) {
        StudentAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $attempt->id,
                'question_id' => $essayQuestion->id,
            ],
            [
                'answer' => 'Jawaban essay dari E2E test',
                'selected_option_id' => null,
                'is_correct' => null,
                'score_awarded' => 0,
            ]
        );
    }

    $this->browse(function (Browser $browser) use ($scenario, $exam) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/teacher/login')
            ->waitFor('input[name="email"]', 15)
            ->waitFor('input[name="password"]', 15)
            ->type('input[name="email"]', $scenario['teacherUser']->email)
            ->type('input[name="password"]', 'password')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/teacher/dashboard', 15)
            ->pause(1000)
            ->visit("/teacher/grading/{$exam->id}/{$scenario['student']->id}")
            ->waitFor("input[type='number']")
            ->pause(1000)
            ->clear("input[type='number']")
            ->type("input[type='number']", '25')
            ->waitForLivewire()->press('SIMPAN & SELESAI')
            ->waitUntilMissingText('Processing', 15)
            ->waitUntilMissingText('Loading', 15)
            ->pause(1000)
            ->waitUsing(15, 100, fn () => str_contains($browser->driver->getCurrentURL(), "/teacher/grading/{$exam->id}"))
            ->screenshot('final_result')
            ->pause(1000);
    });

    $attempt->refresh();
    expect((string) $attempt->status->value)->toBe('graded');

    $this->browse(function (Browser $browser) use ($scenario, $attempt) {
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit('/student/login')
            ->waitFor('input[name="email"]', 15)
            ->waitFor('input[name="password"]', 15)
            ->type('input[name="email"]', $scenario['studentUser']->email)
            ->type('input[name="password"]', '20261234')
            ->press('MASUK DASHBOARD')
            ->waitForLocation('/student/dashboard', 15)
            ->pause(1000)
            ->visit("/student/results/{$attempt->id}")
            ->pause(1000)
            ->assertSee('NILAI AKHIR')
            ->assertSee((string) number_format((float) $attempt->percentage, 1, '.', ''));
    });
});
