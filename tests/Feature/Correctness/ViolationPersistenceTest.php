<?php

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('persists violation counter server-side and enforces tab tolerance via statusCheck', function () {
    $this->withoutVite();

    $subject = Subject::create(['name' => 'Math', 'code' => 'MATH-VIO']);

    $teacherUser = User::create([
        'name' => 'Teacher Vio',
        'email' => 'teacher.vio@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student Vio',
        'email' => 'student.vio@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Violation Exam',
        'date' => now()->toDateString(),
        'start_time' => '00:01',
        'end_time' => '23:59',
        'duration_minutes' => 120,
        'token' => 'VIO001',
        'passing_grade' => 60,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => true,
        'tab_tolerance' => 2,
        'status' => 'scheduled',
    ]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
        'tab_switches' => 0,
    ]);

    $this->actingAs($studentUser)
        ->postJson(route('student.exam.log-violation', $exam->id), [
            'type' => 'tab_switch',
            'message' => 'left tab',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'tab_switches' => 1]);

    $attempt->refresh();
    expect((int) $attempt->tab_switches)->toBe(1);

    // Second violation reaches tolerance.
    $this->actingAs($studentUser)
        ->postJson(route('student.exam.log-violation', $exam->id), [
            'type' => 'tab_switch',
            'message' => 'left tab again',
        ])
        ->assertOk()
        ->assertJson(['success' => true, 'tab_switches' => 2, 'force_stop' => true]);

    $this->actingAs($studentUser)
        ->getJson(route('student.exam.status_check', $exam->id))
        ->assertOk()
        ->assertJson(['force_stop' => true]);
});

it('auto finalizes attempt as completed when violation tolerance is reached', function () {
    $this->withoutVite();

    $subject = Subject::create(['name' => 'Physics', 'code' => 'PHY-VIO']);

    $teacherUser = User::create([
        'name' => 'Teacher AutoStop',
        'email' => 'teacher.autostop@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student AutoStop',
        'email' => 'student.autostop@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Auto Stop Exam',
        'date' => now()->toDateString(),
        'start_time' => '00:01',
        'end_time' => '23:59',
        'duration_minutes' => 120,
        'token' => 'AST001',
        'passing_grade' => 60,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => true,
        'tab_tolerance' => 1,
        'status' => 'scheduled',
    ]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
        'tab_switches' => 0,
    ]);

    $this->actingAs($studentUser)
        ->postJson(route('student.exam.log-violation', $exam->id), [
            'type' => 'tab_switch',
            'message' => 'left tab',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'force_stop' => true,
        ]);

    $attempt->refresh();

    expect($attempt->submitted_at)->not->toBeNull();
    expect($attempt->status)->toBe(ExamAttemptStatus::Completed);
});
