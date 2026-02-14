<?php

use App\Enums\ExamAttemptStatus;
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

uses(RefreshDatabase::class);

it('shows pending status on teacher student report detail when essay is not graded', function () {
    $teacherUser = User::create([
        'name' => 'Teacher Report',
        'email' => 'teacher.report.pending@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student Report',
        'email' => 'student.report.pending@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
    $classroom = Classroom::create(['name' => 'X-RPT-1', 'level' => 'X']);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'nis' => 'RPT001',
        'classroom_id' => $classroom->id,
    ]);

    $subject = Subject::create(['name' => 'Bahasa Indonesia', 'code' => 'RPT-BIN']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Ujian Report Pending',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'RPT001',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);
    $exam->classrooms()->attach($classroom->id);

    $essay = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group Report',
        'type' => 'essay',
        'text' => 'Jelaskan makna puisi.',
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

    StudentAnswer::create([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $essay->id,
        'answer' => 'Jawaban siswa',
        'is_correct' => null,
        'score_awarded' => 0,
    ]);

    $this->actingAs($teacherUser)
        ->get(route('teacher.reports.student', ['examId' => $exam->id, 'studentId' => $student->id]))
        ->assertOk()
        ->assertSee('PENDING PENILAIAN')
        ->assertDontSee('GAGAL');
});
