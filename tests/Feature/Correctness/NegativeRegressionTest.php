<?php

use App\Livewire\Teacher\Exam\Index as TeacherExamIndex;
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
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('forbids teacher from updating and deleting an exam they do not own', function () {
    $this->withoutVite();

    [$teacherAUser, ] = makeTeacherUserForNegativeTests('Teacher A', 'teacher.a.neg@example.test');
    [, $teacherB] = makeTeacherUserForNegativeTests('Teacher B', 'teacher.b.neg@example.test');
    $subject = Subject::create(['name' => 'Math', 'code' => 'NEG-MATH']);

    $exam = Exam::create([
        'teacher_id' => $teacherB->id,
        'subject_id' => $subject->id,
        'name' => 'Teacher B Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA01',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $this->actingAs($teacherAUser)
        ->get(route('teacher.exams.edit', $exam->id))
        ->assertForbidden();

    Livewire::actingAs($teacherAUser)
        ->test(TeacherExamIndex::class)
        ->call('openDeleteModal', $exam->id)
        ->assertForbidden();

    $this->assertDatabaseHas('exams', ['id' => $exam->id]);
});

it('rejects saveAnswer for attempts that are no longer in progress', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.completed.neg@example.test');
    [$studentUser, $student] = makeStudentUserForNegativeTests('Student', 'student.completed.neg@example.test', 'NEG001');
    $subject = Subject::create(['name' => 'Science', 'code' => 'NEG-SCI']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Stale Attempt Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA02',
        'passing_grade' => 70,
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
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => 'Question',
        'score' => 10,
    ]);
    $option = QuestionOption::create([
        'question_id' => $question->id,
        'label' => 'A',
        'text' => 'Option A',
        'is_correct' => true,
    ]);
    $exam->questions()->attach($question->id, ['order' => 1, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(10),
        'status' => ExamAttemptStatus::Submitted,
        'submitted_at' => now()->subMinute(),
    ]);

    $this->actingAs($studentUser)
        ->postJson(route('student.exam.save-answer', $exam->id), [
            'question_id' => $question->id,
            'answer' => $option->id,
        ])
        ->assertStatus(404);

    $this->assertDatabaseMissing('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ]);
});

it('rejects saveAnswer when attempt has timed out', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.timeout.neg@example.test');
    [$studentUser, $student] = makeStudentUserForNegativeTests('Student', 'student.timeout.neg@example.test', 'NEG002');
    $subject = Subject::create(['name' => 'History', 'code' => 'NEG-HIS']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Timed Out Attempt Exam',
        'date' => now()->toDateString(),
        'start_time' => '00:01',
        'end_time' => '23:59',
        'duration_minutes' => 60,
        'token' => 'NEGA03',
        'passing_grade' => 70,
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
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => 'Question',
        'score' => 10,
    ]);
    $option = QuestionOption::create([
        'question_id' => $question->id,
        'label' => 'A',
        'text' => 'Option A',
        'is_correct' => true,
    ]);
    $exam->questions()->attach($question->id, ['order' => 1, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(90), // duration 60 + buffer 1 minute exceeded
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $this->actingAs($studentUser)
        ->postJson(route('student.exam.save-answer', $exam->id), [
            'question_id' => $question->id,
            'answer' => $option->id,
        ])
        ->assertStatus(403);

    $this->assertDatabaseMissing('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ]);
});

it('prevents student role from accessing grading and report routes', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.exposure.neg@example.test');
    [$studentUser, $student] = makeStudentUserForNegativeTests('Student', 'student.exposure.neg@example.test', 'NEG003');
    $subject = Subject::create(['name' => 'Biology', 'code' => 'NEG-BIO']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Resource Exposure Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA04',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $this->actingAs($studentUser)
        ->get(route('teacher.grading.show', $exam->id))
        ->assertForbidden();

    $this->actingAs($studentUser)
        ->get(route('teacher.reports.detail', $exam->id))
        ->assertForbidden();

    $this->actingAs($studentUser)
        ->get(route('teacher.reports.analysis', $exam->id))
        ->assertForbidden();
});

it('recalculates total score using latest exam question pivot scores', function () {
    $this->withoutVite();

    $scoringService = app(ScoringService::class);

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.pivot.neg@example.test');
    [, $student] = makeStudentUserForNegativeTests('Student', 'student.pivot.neg@example.test', 'NEG004');
    $subject = Subject::create(['name' => 'Physics', 'code' => 'NEG-PHY']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Pivot Integrity Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA05',
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

    $q1Correct = QuestionOption::create(['question_id' => $q1->id, 'label' => 'A', 'text' => 'Correct', 'is_correct' => true]);
    $q2Correct = QuestionOption::create(['question_id' => $q2->id, 'label' => 'A', 'text' => 'Correct', 'is_correct' => true]);

    $exam->questions()->attach($q1->id, ['order' => 1, 'score' => 10]);
    $exam->questions()->attach($q2->id, ['order' => 2, 'score' => 20]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    StudentAnswer::create(array_merge([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $q1->id,
    ], $scoringService->scoreSingleAnswer($exam, $q1, $q1Correct->id)));

    StudentAnswer::create(array_merge([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $q2->id,
    ], $scoringService->scoreSingleAnswer($exam, $q2, $q2Correct->id)));

    $initial = $scoringService->recalculateAttempt($exam, $attempt);
    expect((int) $initial['total_score'])->toBe(30);

    $exam->questions()->updateExistingPivot($q1->id, ['score' => 40]);

    $updated = $scoringService->recalculateAttempt($exam->fresh(), $attempt->fresh());

    expect((int) $updated['total_score'])->toBe(60)
        ->and((float) $updated['percentage'])->toBe(100.0);

    $this->assertDatabaseHas('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $q1->id,
        'score_awarded' => 40,
    ]);
});

it('updates last_seen_at through heartbeat for active attempt', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.heartbeat.neg@example.test');
    [$studentUser, $student] = makeStudentUserForNegativeTests('Student', 'student.heartbeat.neg@example.test', 'NEG005');
    $subject = Subject::create(['name' => 'Chemistry', 'code' => 'NEG-CHEM']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Heartbeat Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA06',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::InProgress,
        'last_seen_at' => null,
    ]);

    $this->actingAs($studentUser)
        ->postJson(route('student.exam.heartbeat', $exam->id))
        ->assertOk()
        ->assertJson(['success' => true]);

    $attempt->refresh();
    expect($attempt->last_seen_at)->not->toBeNull();
});

it('rejects heartbeat for non-active attempt', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.heartbeat2.neg@example.test');
    [$studentUser, $student] = makeStudentUserForNegativeTests('Student', 'student.heartbeat2.neg@example.test', 'NEG006');
    $subject = Subject::create(['name' => 'Geography', 'code' => 'NEG-GEO']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Heartbeat Finalized Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA07',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(30),
        'submitted_at' => now()->subMinute(),
        'status' => ExamAttemptStatus::Submitted,
    ]);

    $this->actingAs($studentUser)
        ->postJson(route('student.exam.heartbeat', $exam->id))
        ->assertStatus(404)
        ->assertJson(['success' => false]);
});

it('scores text answers case-insensitively and with trimmed whitespace', function () {
    $this->withoutVite();

    $scoringService = app(ScoringService::class);

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.normalize.neg@example.test');
    [, $student] = makeStudentUserForNegativeTests('Student', 'student.normalize.neg@example.test', 'NEG007');
    $subject = Subject::create(['name' => 'Language', 'code' => 'NEG-LNG']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Normalize Answer Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA08',
        'passing_grade' => 70,
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
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => 'Pilih jawaban benar',
        'score' => 10,
    ]);

    QuestionOption::create([
        'question_id' => $question->id,
        'label' => 'A',
        'text' => 'Benar',
        'is_correct' => true,
    ]);

    $exam->questions()->attach($question->id, ['order' => 1, 'score' => 25]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    StudentAnswer::create(array_merge([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ], $scoringService->scoreSingleAnswer($exam, $question, '  benar  ')));

    $result = $scoringService->recalculateAttempt($exam, $attempt->fresh());

    expect((int) $result['total_score'])->toBe(25);

    $this->assertDatabaseHas('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'is_correct' => true,
        'score_awarded' => 25,
    ]);
});

it('does not downgrade previously correct mc answers when selected option reference becomes stale', function () {
    $this->withoutVite();

    $scoringService = app(ScoringService::class);

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.stale-option.neg@example.test');
    [, $student] = makeStudentUserForNegativeTests('Student', 'student.stale-option.neg@example.test', 'NEG009');
    $subject = Subject::create(['name' => 'Math', 'code' => 'NEG-STL']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Stale Option Integrity Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA09',
        'passing_grade' => 70,
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
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => '2 + 2 = ?',
        'score' => 10,
    ]);

    $correctOption = QuestionOption::create([
        'question_id' => $question->id,
        'label' => 'A',
        'text' => '4',
        'is_correct' => true,
    ]);

    $exam->questions()->attach($question->id, ['order' => 1, 'score' => 25]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $studentAnswer = StudentAnswer::create(array_merge([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $question->id,
    ], $scoringService->scoreSingleAnswer($exam, $question, $correctOption->id)));

    // Simulate stale FK reference after option list mutation.
    $correctOption->delete();

    $result = $scoringService->recalculateAttempt($exam, $attempt->fresh());

    $studentAnswer->refresh();

    expect((int) $result['total_score'])->toBe(25)
        ->and($studentAnswer->is_correct)->toBeTrue()
        ->and((int) $studentAnswer->score_awarded)->toBe(25);
});

it('auto-remaps mismatched selected_option_id by label during recalculation', function () {
    $this->withoutVite();

    $scoringService = app(ScoringService::class);

    [, $teacher] = makeTeacherUserForNegativeTests('Teacher', 'teacher.remap.neg@example.test');
    [, $student] = makeStudentUserForNegativeTests('Student', 'student.remap.neg@example.test', 'NEG010');
    $subject = Subject::create(['name' => 'Math', 'code' => 'NEG-RMP']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Remap Option Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'NEGA10',
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
        'text' => '2 + 2 = ?',
        'score' => 10,
    ]);

    $questionB = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => '3 + 3 = ?',
        'score' => 10,
    ]);

    $correctA = QuestionOption::create([
        'question_id' => $questionA->id,
        'label' => 'A',
        'text' => '4',
        'is_correct' => true,
    ]);

    $wrongA = QuestionOption::create([
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
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $answer = StudentAnswer::create([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $questionA->id,
        'selected_option_id' => $wrongA->id, // points to another question
        'answer' => (string) $wrongA->id,
        'is_correct' => false,
        'score_awarded' => 0,
    ]);

    $result = $scoringService->recalculateAttempt($exam, $attempt);
    $answer->refresh();

    expect((int) $result['total_score'])->toBe(25)
        ->and((int) $answer->selected_option_id)->toBe((int) $correctA->id)
        ->and($answer->is_correct)->toBeTrue()
        ->and((int) $answer->score_awarded)->toBe(25);
});

function makeTeacherUserForNegativeTests(string $name, string $email): array
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

function makeStudentUserForNegativeTests(string $name, string $email, string $nis): array
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
