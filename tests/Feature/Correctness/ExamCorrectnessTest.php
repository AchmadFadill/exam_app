<?php

use App\Imports\TeachersImport;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('prevents teacher from accessing another teacher grading and reports', function () {
    $this->withoutVite();

    [$teacherAUser, $teacherA] = makeTeacherUser('Teacher A', 'teacher.a@example.test');
    [, $teacherB] = makeTeacherUser('Teacher B', 'teacher.b@example.test');
    $subject = Subject::create(['name' => 'Math', 'code' => 'MATH']);

    $classroom = Classroom::create([
        'name' => 'X-A',
        'level' => 'X',
    ]);

    $exam = Exam::create([
        'teacher_id' => $teacherB->id,
        'subject_id' => $subject->id,
        'name' => 'Exam B',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'TOKB01',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);
    $exam->classrooms()->attach($classroom->id);

    Question::create([
        'teacher_id' => $teacherB->id,
        'subject_id' => $subject->id,
        'title' => 'Group B',
        'type' => 'essay',
        'text' => 'Explain integrals',
        'score' => 10,
    ]);

    $this->actingAs($teacherAUser)
        ->get(route('teacher.grading.show', $exam->id))
        ->assertForbidden();

    $this->actingAs($teacherAUser)
        ->get(route('teacher.reports.detail', $exam->id))
        ->assertForbidden();

    $this->actingAs($teacherAUser)
        ->get(route('teacher.questions.group', ['title' => urlencode('Group B')]))
        ->assertForbidden();
});

it('rejects tampered question id on save-answer when question does not belong to exam', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUser('Teacher', 'teacher@example.test');
    [$studentUser, $student] = makeStudentUser('Student', 'student@example.test', 'NIS001');
    $subject = Subject::create(['name' => 'Science', 'code' => 'SCI']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam 1',
        'date' => now()->toDateString(),
        'start_time' => now()->subHour()->format('H:i'),
        'end_time' => now()->addHour()->format('H:i'),
        'duration_minutes' => 120,
        'token' => 'TOKA01',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $questionInExam = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Exam1',
        'type' => 'multiple_choice',
        'text' => '2 + 2 = ?',
        'score' => 10,
    ]);

    $tamperedQuestion = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Exam2',
        'type' => 'multiple_choice',
        'text' => '3 + 3 = ?',
        'score' => 10,
    ]);

    $exam->questions()->attach($questionInExam->id, ['order' => 1, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now(),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $response = $this->actingAs($studentUser)->postJson(route('student.exam.save-answer', $exam->id), [
        'question_id' => $tamperedQuestion->id,
        'answer' => '1',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $tamperedQuestion->id,
    ]);
});

it('computes final score and pass fail from exam question pivot scores on submit flow', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUser('Teacher', 'teacher.score@example.test');
    [$studentUser, $student] = makeStudentUser('Student', 'student.score@example.test', 'NIS100');
    $subject = Subject::create(['name' => 'Physics', 'code' => 'PHY']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Scoring Exam',
        'date' => now()->toDateString(),
        'start_time' => now()->subHour()->format('H:i'),
        'end_time' => now()->addHour()->format('H:i'),
        'duration_minutes' => 120,
        'token' => 'TOKS01',
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
        'text' => 'Question 1',
        'score' => 5,
    ]);
    $q2 = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set A',
        'type' => 'multiple_choice',
        'text' => 'Question 2',
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

    $this->actingAs($studentUser)->postJson(route('student.exam.save-answer', $exam->id), [
        'question_id' => $q1->id,
        'answer' => $q1Correct->id,
    ])->assertOk();

    $this->actingAs($studentUser)->postJson(route('student.exam.submit', $exam->id), [
        'answers' => [
            $q1->id => $q1Correct->id,
            $q2->id => $q2Wrong->id,
        ],
    ])->assertOk();

    $attempt->refresh();

    expect((int) $attempt->total_score)->toBe(30)
        ->and((float) $attempt->percentage)->toBe(30.0)
        ->and($attempt->passed)->toBeFalse()
        ->and($attempt->status)->toBe(ExamAttemptStatus::Graded)
        ->and($attempt->submitted_at)->not->toBeNull();
});

it('imports teacher subject assignment through pivot without removed subject_id field', function () {
    $subject = Subject::create(['name' => 'Matematika', 'code' => 'MATH']);

    $import = new TeachersImport();
    $import->collection(new Collection([
        [
            'nama' => 'Guru Import',
            'email' => 'guru.import@example.test',
            'mata_pelajaran' => 'MATH',
        ],
    ]));

    $teacher = Teacher::firstOrFail();
    $this->assertDatabaseHas('users', ['email' => 'guru.import@example.test', 'role' => 'teacher']);
    $this->assertDatabaseHas('subject_teacher', [
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
    ]);
});

function makeTeacherUser(string $name, string $email): array
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

function makeStudentUser(string $name, string $email, string $nis): array
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
