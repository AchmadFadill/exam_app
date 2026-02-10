<?php

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function buildAuthorizationScenario(): array
{
    $subject = Subject::create(['name' => 'Policy Subject', 'code' => 'PLC01']);
    $classroom = Classroom::create(['name' => 'XI Auth', 'level' => 'XI']);

    $teacherAUser = User::create([
        'name' => 'Teacher A',
        'email' => 'teacher-a@policy.test',
        'password' => Hash::make('password'),
        'role' => 'teacher',
    ]);
    $teacherA = Teacher::create(['user_id' => $teacherAUser->id]);
    $teacherA->subjects()->sync([$subject->id]);

    $teacherBUser = User::create([
        'name' => 'Teacher B',
        'email' => 'teacher-b@policy.test',
        'password' => Hash::make('password'),
        'role' => 'teacher',
    ]);
    $teacherB = Teacher::create(['user_id' => $teacherBUser->id]);
    $teacherB->subjects()->sync([$subject->id]);

    $studentUser = User::create([
        'name' => 'Student Policy',
        'email' => 'student@policy.test',
        'password' => Hash::make('20269999'),
        'role' => 'student',
    ]);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'nis' => '20269999',
        'classroom_id' => $classroom->id,
    ]);

    $examOwnedByB = Exam::create([
        'teacher_id' => $teacherB->id,
        'subject_id' => $subject->id,
        'name' => 'Teacher B Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00:00',
        'end_time' => '10:00:00',
        'duration_minutes' => 120,
        'token' => 'POL123',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);
    $examOwnedByB->classrooms()->sync([$classroom->id]);

    $question = Question::create([
        'teacher_id' => $teacherB->id,
        'subject_id' => $subject->id,
        'title' => 'Auth Group',
        'type' => 'essay',
        'text' => 'Auth essay',
        'score' => 10,
    ]);
    $examOwnedByB->questions()->sync([$question->id => ['order' => 1, 'score' => 10]]);

    ExamAttempt::create([
        'exam_id' => $examOwnedByB->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(30),
        'submitted_at' => now()->subMinutes(5),
        'status' => 'submitted',
        'total_score' => 0,
        'percentage' => 0,
        'passed' => false,
    ]);

    return compact('teacherAUser', 'teacherBUser', 'studentUser', 'student', 'examOwnedByB');
}

it('returns 403 when teacher accesses another teachers grading and report routes', function () {
    $data = buildAuthorizationScenario();

    $this->actingAs($data['teacherAUser'])
        ->get(route('teacher.exams.edit', ['id' => $data['examOwnedByB']->id]))
        ->assertForbidden();

    $this->actingAs($data['teacherAUser'])
        ->get(route('teacher.reports.detail', ['id' => $data['examOwnedByB']->id]))
        ->assertForbidden();
});

it('blocks students from grading and report routes even with valid exam id', function () {
    $data = buildAuthorizationScenario();

    $this->actingAs($data['studentUser'])
        ->get(route('teacher.grading.show', ['exam' => $data['examOwnedByB']->id]))
        ->assertForbidden();

    $this->actingAs($data['studentUser'])
        ->get(route('teacher.reports.detail', ['id' => $data['examOwnedByB']->id]))
        ->assertForbidden();
});
