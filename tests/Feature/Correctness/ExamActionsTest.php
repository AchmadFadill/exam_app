<?php

use App\Actions\Exam\DuplicateExamAction;
use App\Actions\Exam\ProcessExamSubmissionAction;
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

uses(RefreshDatabase::class);

it('duplicates exam with classrooms and question pivot scores', function () {
    [, $teacher] = makeTeacherUserForActions('Teacher Actions', 'teacher.actions@example.test');
    $subject = Subject::create(['name' => 'Math', 'code' => 'ACTMTH']);

    $classroomA = Classroom::create(['name' => 'X-ACT-1', 'level' => 'X']);
    $classroomB = Classroom::create(['name' => 'X-ACT-2', 'level' => 'X']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam Original',
        'date' => now()->subDay()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'ACT001',
        'passing_grade' => 70,
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
        'text' => 'Q1',
        'score' => 5,
    ]);
    $q2 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => 'Q2',
        'score' => 5,
    ]);

    $exam->questions()->attach($q1->id, ['order' => 1, 'score' => 40]);
    $exam->questions()->attach($q2->id, ['order' => 2, 'score' => 60]);
    $exam->classrooms()->sync([$classroomA->id, $classroomB->id]);

    $copy = app(DuplicateExamAction::class)->execute($exam->fresh(['questions', 'classrooms']));

    expect($copy->id)->not->toBe($exam->id)
        ->and($copy->name)->toBe('Salinan - ' . $exam->name)
        ->and($copy->status)->toBe('draft')
        ->and($copy->date->toDateString())->toBe(now()->toDateString())
        ->and($copy->token)->not->toBe($exam->token);

    $copy->load(['questions', 'classrooms']);

    $this->assertCount(2, $copy->questions);
    $this->assertCount(2, $copy->classrooms);
    $this->assertDatabaseHas('exam_questions', ['exam_id' => $copy->id, 'question_id' => $q1->id, 'order' => 1, 'score' => 40]);
    $this->assertDatabaseHas('exam_questions', ['exam_id' => $copy->id, 'question_id' => $q2->id, 'order' => 2, 'score' => 60]);
});

it('processes exam submission and computes score through action', function () {
    $process = app(ProcessExamSubmissionAction::class);

    [, $teacher] = makeTeacherUserForActions('Teacher Submission', 'teacher.submit.action@example.test');
    [, $student] = makeStudentUserForActions('Student Submission', 'student.submit.action@example.test', 'ACT001');
    $subject = Subject::create(['name' => 'Science', 'code' => 'ACTSCI']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Action Submission Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'ACT002',
        'passing_grade' => 50,
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
        'title' => 'Set B',
        'type' => 'multiple_choice',
        'text' => 'Q1',
        'score' => 5,
    ]);
    $q2 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set B',
        'type' => 'multiple_choice',
        'text' => 'Q2',
        'score' => 5,
    ]);

    $q1Correct = QuestionOption::create(['question_id' => $q1->id, 'label' => 'A', 'text' => 'Correct', 'is_correct' => true]);
    QuestionOption::create(['question_id' => $q1->id, 'label' => 'B', 'text' => 'Wrong', 'is_correct' => false]);
    QuestionOption::create(['question_id' => $q2->id, 'label' => 'A', 'text' => 'Correct', 'is_correct' => true]);
    $q2Wrong = QuestionOption::create(['question_id' => $q2->id, 'label' => 'B', 'text' => 'Wrong', 'is_correct' => false]);

    $exam->questions()->attach($q1->id, ['order' => 1, 'score' => 30]);
    $exam->questions()->attach($q2->id, ['order' => 2, 'score' => 70]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $processed = $process->execute($exam->fresh('questions'), $attempt, [
        $q1->id => $q1Correct->id,
        $q2->id => $q2Wrong->id,
    ]);

    expect($processed->status)->toBe(ExamAttemptStatus::Graded)
        ->and((int) $processed->total_score)->toBe(30)
        ->and((float) $processed->percentage)->toBe(30.0)
        ->and($processed->submitted_at)->not->toBeNull();

    $this->assertDatabaseHas('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $q1->id,
        'score_awarded' => 30,
    ]);
});

function makeTeacherUserForActions(string $name, string $email): array
{
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);

    $teacher = Teacher::create(['user_id' => $user->id]);

    return [$user, $teacher];
}

function makeStudentUserForActions(string $name, string $email, string $nis): array
{
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);

    $classroom = Classroom::create([
        'name' => 'X-ACT-' . $nis,
        'level' => 'X',
    ]);

    $student = Student::create([
        'user_id' => $user->id,
        'nis' => $nis,
        'classroom_id' => $classroom->id,
    ]);

    return [$user, $student];
}
