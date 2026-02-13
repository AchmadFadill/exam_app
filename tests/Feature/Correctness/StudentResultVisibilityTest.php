<?php

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

it('hides score in student result list when exam setting disables score visibility', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForResultVisibility('Teacher Visibility', 'teacher.visibility@example.test');
    [$studentUser, $student] = makeStudentUserForResultVisibility('Student Visibility', 'student.visibility@example.test', 'VIS001');
    $subject = Subject::create(['name' => 'Math', 'code' => 'VIS-MATH']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam Hidden Score',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'VIS001',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'show_score_to_student' => false,
        'show_answers_to_student' => true,
        'status' => 'scheduled',
    ]);

    ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(20),
        'submitted_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::Graded,
        'total_score' => 80,
        'percentage' => 80.0,
        'passed' => true,
    ]);

    $this->actingAs($studentUser)
        ->get(route('student.results'))
        ->assertOk()
        ->assertSee('Disembunyikan');
});

it('shows hidden notice on result detail when answer visibility is disabled', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForResultVisibility('Teacher Visibility 2', 'teacher.visibility2@example.test');
    [$studentUser, $student] = makeStudentUserForResultVisibility('Student Visibility 2', 'student.visibility2@example.test', 'VIS002');
    $subject = Subject::create(['name' => 'Science', 'code' => 'VIS-SCI']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam Hidden Answers',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'VIS002',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'show_score_to_student' => true,
        'show_answers_to_student' => false,
        'status' => 'scheduled',
    ]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(20),
        'submitted_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::Graded,
        'total_score' => 70,
        'percentage' => 70.0,
        'passed' => true,
    ]);

    $this->actingAs($studentUser)
        ->get(route('student.results.detail', $attempt->id))
        ->assertOk()
        ->assertSee('Pembahasan jawaban disembunyikan oleh guru untuk ujian ini.');
});

it('redirects submit response to result detail even when answer visibility is disabled', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForResultVisibility('Teacher Visibility 3', 'teacher.visibility3@example.test');
    [$studentUser, $student] = makeStudentUserForResultVisibility('Student Visibility 3', 'student.visibility3@example.test', 'VIS003');
    $subject = Subject::create(['name' => 'History', 'code' => 'VIS-HIS']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam Redirect',
        'date' => now()->toDateString(),
        'start_time' => now()->subHour()->format('H:i'),
        'end_time' => now()->addHour()->format('H:i'),
        'duration_minutes' => 120,
        'token' => 'VIS003',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'show_score_to_student' => true,
        'show_answers_to_student' => false,
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

    $exam->questions()->attach($question->id, ['order' => 1, 'score' => 10]);

    ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::InProgress,
    ]);

    $response = $this->actingAs($studentUser)
        ->postJson(route('student.exam.submit', $exam->id), [
            'answers' => [$question->id => $correctOption->id],
        ]);

    $attemptId = ExamAttempt::query()
        ->where('exam_id', $exam->id)
        ->where('student_id', $student->id)
        ->value('id');

    $response->assertOk()
        ->assertJsonPath('redirect', route('student.results.detail', $attemptId));
});

it('shows pending badge on result list when essay grading is not finished', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForResultVisibility('Teacher Visibility 4', 'teacher.visibility4@example.test');
    [$studentUser, $student] = makeStudentUserForResultVisibility('Student Visibility 4', 'student.visibility4@example.test', 'VIS004');
    $subject = Subject::create(['name' => 'Bahasa', 'code' => 'VIS-BHS']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam Pending Essay',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'VIS004',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'show_score_to_student' => true,
        'show_answers_to_student' => true,
        'status' => 'scheduled',
    ]);

    $essay = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set Essay',
        'type' => 'essay',
        'text' => 'Jelaskan fotosintesis',
        'score' => 10,
    ]);

    $exam->questions()->attach($essay->id, ['order' => 1, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(30),
        'submitted_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::Submitted,
        'total_score' => 0,
        'percentage' => 0,
        'passed' => false,
    ]);

    $attempt->answers()->create([
        'question_id' => $essay->id,
        'answer_text' => 'Jawaban siswa',
        'selected_option_id' => null,
        'is_correct' => null,
        'score_awarded' => 0,
    ]);

    $this->actingAs($studentUser)
        ->get(route('student.results'))
        ->assertOk()
        ->assertSee('Pending Penilaian');
});

it('shows pending badge on result detail when essay grading is not finished', function () {
    $this->withoutVite();

    [, $teacher] = makeTeacherUserForResultVisibility('Teacher Visibility 5', 'teacher.visibility5@example.test');
    [$studentUser, $student] = makeStudentUserForResultVisibility('Student Visibility 5', 'student.visibility5@example.test', 'VIS005');
    $subject = Subject::create(['name' => 'Fisika', 'code' => 'VIS-FIS']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Exam Pending Detail',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'VIS005',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'show_score_to_student' => true,
        'show_answers_to_student' => true,
        'status' => 'scheduled',
    ]);

    $essay = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Set Essay',
        'type' => 'essay',
        'text' => 'Jelaskan gaya gravitasi.',
        'score' => 10,
    ]);
    $exam->questions()->attach($essay->id, ['order' => 1, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(20),
        'submitted_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::Submitted,
        'total_score' => 0,
        'percentage' => 0,
        'passed' => false,
    ]);

    $attempt->answers()->create([
        'question_id' => $essay->id,
        'answer_text' => 'Bumi menarik benda...',
        'selected_option_id' => null,
        'is_correct' => null,
        'score_awarded' => 0,
    ]);

    $this->actingAs($studentUser)
        ->get(route('student.results.detail', $attempt->id))
        ->assertOk()
        ->assertSee('PENDING PENILAIAN')
        ->assertDontSee('TIDAK LULUS');
});

function makeTeacherUserForResultVisibility(string $name, string $email): array
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

function makeStudentUserForResultVisibility(string $name, string $email, string $nis): array
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
