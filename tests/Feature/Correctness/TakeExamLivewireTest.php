<?php

use App\Livewire\Student\TakeExam;
use App\Enums\ExamAttemptStatus;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('persists current answer when navigating to next question', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForTakeExam('Teacher Nav', 'teacher.nav@example.test');
    [$studentUser, $student] = makeStudentUserForTakeExam('Student Nav', 'student.nav@example.test', 'NISNAV01');
    $subject = Subject::create(['name' => 'Math', 'code' => 'MTHNAV']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Navigation Exam',
        'date' => now()->toDateString(),
        'start_time' => '00:01',
        'end_time' => '23:59',
        'duration_minutes' => 120,
        'token' => 'NAV001',
        'passing_grade' => 60,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $q1 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => '1 + 1 = ?',
        'score' => 10,
    ]);
    $q2 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => '2 + 2 = ?',
        'score' => 10,
    ]);

    $q1Correct = QuestionOption::create(['question_id' => $q1->id, 'label' => 'A', 'text' => '2', 'is_correct' => true]);
    QuestionOption::create(['question_id' => $q1->id, 'label' => 'B', 'text' => '3', 'is_correct' => false]);
    QuestionOption::create(['question_id' => $q2->id, 'label' => 'A', 'text' => '4', 'is_correct' => true]);
    QuestionOption::create(['question_id' => $q2->id, 'label' => 'B', 'text' => '5', 'is_correct' => false]);

    $exam->questions()->attach($q1->id, ['order' => 1, 'score' => 30]);
    $exam->questions()->attach($q2->id, ['order' => 2, 'score' => 70]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    Livewire::actingAs($studentUser)
        ->test(TakeExam::class, ['id' => $exam->id])
        ->set('selectedOption', $q1Correct->id)
        ->call('nextQuestion')
        ->assertSet('currentQuestionIndex', 1);

    $this->assertDatabaseHas('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $q1->id,
        'selected_option_id' => $q1Correct->id,
        'score_awarded' => 30,
    ]);
});

it('keeps final scoring consistent by saving current answer during timeout submit', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForTakeExam('Teacher Timeout', 'teacher.timeout@example.test');
    [$studentUser, $student] = makeStudentUserForTakeExam('Student Timeout', 'student.timeout@example.test', 'NISTIME01');
    $subject = Subject::create(['name' => 'Science', 'code' => 'SCITIME']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Timeout Exam',
        'date' => now()->toDateString(),
        'start_time' => '00:01',
        'end_time' => '23:59',
        'duration_minutes' => 120,
        'token' => 'TIME01',
        'passing_grade' => 50,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $question = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set T',
        'type' => 'multiple_choice',
        'text' => 'Earth is a ...',
        'score' => 10,
    ]);

    $correctOption = QuestionOption::create(['question_id' => $question->id, 'label' => 'A', 'text' => 'Planet', 'is_correct' => true]);
    QuestionOption::create(['question_id' => $question->id, 'label' => 'B', 'text' => 'Star', 'is_correct' => false]);
    $exam->questions()->attach($question->id, ['order' => 1, 'score' => 100]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $component = Livewire::actingAs($studentUser)
        ->test(TakeExam::class, ['id' => $exam->id])
        ->set('selectedOption', $correctOption->id);

    // Simulate answer selected just before submission while server-side deadline has passed.
    $attempt->update(['started_at' => now()->subMinutes(150)]);

    $component->call('submitExam');

    $attempt->refresh();

    expect((int) $attempt->total_score)->toBe(100)
        ->and((float) $attempt->percentage)->toBe(100.0)
        ->and($attempt->passed)->toBeTrue()
        ->and($attempt->status)->toBe(ExamAttemptStatus::Graded)
        ->and($attempt->submitted_at)->not->toBeNull();
});

it('loads only questions attached to the exam pivot even when same subject has other groups', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForTakeExam('Teacher Group', 'teacher.group@example.test');
    [$studentUser, $student] = makeStudentUserForTakeExam('Student Group', 'student.group@example.test', 'NISGRP01');
    $subject = Subject::create(['name' => 'Geography', 'code' => 'GEOGRP']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Group Strict Exam',
        'date' => now()->toDateString(),
        'start_time' => '00:01',
        'end_time' => '23:59',
        'duration_minutes' => 120,
        'token' => 'GRP001',
        'passing_grade' => 60,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $includedQ1 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group A',
        'type' => 'multiple_choice',
        'text' => 'Included 1',
        'score' => 10,
    ]);
    $includedQ2 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group A',
        'type' => 'multiple_choice',
        'text' => 'Included 2',
        'score' => 10,
    ]);
    $otherGroupQuestion = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group B',
        'type' => 'multiple_choice',
        'text' => 'Must Not Appear',
        'score' => 10,
    ]);

    QuestionOption::create(['question_id' => $includedQ1->id, 'label' => 'A', 'text' => 'X', 'is_correct' => true]);
    QuestionOption::create(['question_id' => $includedQ2->id, 'label' => 'A', 'text' => 'Y', 'is_correct' => true]);
    QuestionOption::create(['question_id' => $otherGroupQuestion->id, 'label' => 'A', 'text' => 'Z', 'is_correct' => true]);

    $exam->questions()->attach($includedQ1->id, ['order' => 1, 'score' => 50]);
    $exam->questions()->attach($includedQ2->id, ['order' => 2, 'score' => 50]);

    ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $component = Livewire::actingAs($studentUser)
        ->test(TakeExam::class, ['id' => $exam->id]);

    $questionIds = $component->instance()->questions->pluck('id')->all();

    expect($questionIds)->toBe([$includedQ1->id, $includedQ2->id])
        ->and($questionIds)->not->toContain($otherGroupQuestion->id);
});

function makeTeacherUserForTakeExam(string $name, string $email): array
{
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);

    $teacher = Teacher::create([
        'user_id' => $user->id,
    ]);

    return [$user, $teacher];
}

function makeStudentUserForTakeExam(string $name, string $email, string $nis): array
{
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);

    $classroom = Classroom::create([
        'name' => 'X-1-' . $nis,
        'level' => 'X',
    ]);

    $student = Student::create([
        'user_id' => $user->id,
        'nis' => $nis,
        'classroom_id' => $classroom->id,
    ]);

    return [$user, $student];
}
