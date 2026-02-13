<?php

use App\Enums\ExamAttemptStatus;
use App\Livewire\Teacher\Grading\Detail as GradingDetail;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows unanswered essay question in grading detail and stores teacher assessment status', function () {
    $actors = makeTeacherAndStudentForEssayGrading();
    [$teacherUser, $teacher] = $actors['teacher'];
    [$studentUser, $student] = $actors['student'];
    $subject = Subject::create(['name' => 'Bahasa', 'code' => 'EGR-BHS']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Ujian Essay',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'EGR001',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $essayAnswered = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Essay Set',
        'type' => 'essay',
        'text' => 'Jelaskan proses fotosintesis.',
        'score' => 10,
    ]);

    $essayEmpty = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Essay Set',
        'type' => 'essay',
        'text' => 'Jelaskan hukum Newton pertama.',
        'score' => 10,
    ]);

    $exam->questions()->attach($essayAnswered->id, ['order' => 1, 'score' => 10]);
    $exam->questions()->attach($essayEmpty->id, ['order' => 2, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(30),
        'submitted_at' => now()->subMinute(),
        'status' => ExamAttemptStatus::Submitted,
    ]);

    StudentAnswer::create([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $essayAnswered->id,
        'answer' => 'Fotosintesis adalah ...',
        'is_correct' => null,
        'score_awarded' => null,
    ]);

    Livewire::actingAs($teacherUser)
        ->test(GradingDetail::class, ['exam' => $exam->id, 'student' => $student->id])
        ->assertSet('essayGrades.' . $essayEmpty->id . '.is_empty', true)
        ->set('essayGrades.' . $essayAnswered->id . '.score', 8)
        ->set('essayGrades.' . $essayEmpty->id . '.score', 0)
        ->call('finishGrading')
        ->assertRedirect(route('teacher.grading.show', ['exam' => $exam->id]));

    $attempt->refresh();

    $this->assertDatabaseHas('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $essayAnswered->id,
        'score_awarded' => 8,
        'is_correct' => true,
    ]);

    $this->assertDatabaseHas('student_answers', [
        'exam_attempt_id' => $attempt->id,
        'question_id' => $essayEmpty->id,
        'score_awarded' => 0,
        'is_correct' => false,
    ]);

    expect($attempt->status)->toBe(ExamAttemptStatus::Graded);
});

function makeTeacherAndStudentForEssayGrading(): array
{
    $teacherUser = User::create([
        'name' => 'Teacher Essay',
        'email' => 'teacher.essay@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student Essay',
        'email' => 'student.essay@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
    $classroom = Classroom::create(['name' => 'X-EGR-1', 'level' => 'X']);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'nis' => 'EGR001',
        'classroom_id' => $classroom->id,
    ]);

    return [
        'teacher' => [$teacherUser, $teacher],
        'student' => [$studentUser, $student],
    ];
}
