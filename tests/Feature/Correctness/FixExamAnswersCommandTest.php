<?php

use App\Enums\ExamAttemptStatus;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('fixes stale selected_option_id by remapping same label and recalculates attempt score', function () {
    $teacherUser = User::create([
        'name' => 'Teacher Fix',
        'email' => 'teacher.fix@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student Fix',
        'email' => 'student.fix@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
    $classroom = Classroom::create(['name' => 'X-FIX', 'level' => 'X']);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'nis' => 'FIX001',
        'classroom_id' => $classroom->id,
    ]);

    $subject = Subject::create(['name' => 'Math', 'code' => 'FIX-MTH']);
    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Fix Command Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'FX001',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $questionA = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => '2 + 2?',
        'score' => 10,
    ]);
    $questionB = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => '3 + 3?',
        'score' => 10,
    ]);

    // Correct answer for question A is label A
    $optionAForQuestionA = QuestionOption::create([
        'question_id' => $questionA->id,
        'label' => 'A',
        'text' => '4',
        'is_correct' => true,
    ]);
    QuestionOption::create([
        'question_id' => $questionA->id,
        'label' => 'B',
        'text' => '5',
        'is_correct' => false,
    ]);

    // Option from another question with same label A (stale/mismatched reference)
    $staleOption = QuestionOption::create([
        'question_id' => $questionB->id,
        'label' => 'A',
        'text' => '6',
        'is_correct' => false,
    ]);

    $exam->questions()->attach($questionA->id, ['order' => 1, 'score' => 25]);
    $exam->questions()->attach($questionB->id, ['order' => 2, 'score' => 25]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'submitted_at' => now(),
        'status' => ExamAttemptStatus::Submitted,
        'total_score' => 0,
        'percentage' => 0,
    ]);

    $answer = StudentAnswer::create([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $questionA->id,
        'selected_option_id' => $staleOption->id, // wrong question reference
        'answer' => (string) $staleOption->id,
        'is_correct' => false,
        'score_awarded' => 0,
    ]);

    $this->artisan('exam:fix-answers', ['--apply' => true])->assertSuccessful();

    $answer->refresh();
    $attempt->refresh();

    expect((int) $answer->selected_option_id)->toBe((int) $optionAForQuestionA->id)
        ->and($answer->is_correct)->toBeTrue()
        ->and((int) $answer->score_awarded)->toBe(25)
        ->and((int) $attempt->total_score)->toBe(25);
});

