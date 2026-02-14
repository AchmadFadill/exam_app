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

it('keeps student score hidden after grading save until teacher publishes exam result', function () {
    $teacherUser = User::create([
        'name' => 'Teacher Publish Gate',
        'email' => 'teacher.publish.gate@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student Publish Gate',
        'email' => 'student.publish.gate@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);
    $classroom = Classroom::create(['name' => 'X-PUB-1', 'level' => 'X']);
    $student = Student::create([
        'user_id' => $studentUser->id,
        'nis' => 'PUB001',
        'classroom_id' => $classroom->id,
    ]);

    $subject = Subject::create(['name' => 'Sosiologi', 'code' => 'PUB-SOS']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Ujian Publish Gate',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'PUB001',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'show_score_to_student' => true,
        'show_answers_to_student' => true,
        'is_published' => false,
        'status' => 'scheduled',
    ]);

    $essay = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group Publish',
        'type' => 'essay',
        'text' => 'Jelaskan konsep utama.',
        'score' => 10,
    ]);
    $exam->questions()->attach($essay->id, ['order' => 1, 'score' => 10]);

    $attempt = ExamAttempt::create([
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'started_at' => now()->subMinutes(20),
        'submitted_at' => now()->subMinutes(5),
        'status' => ExamAttemptStatus::Graded,
        'total_score' => 90,
        'percentage' => 90.0,
        'passed' => true,
    ]);

    StudentAnswer::create([
        'exam_attempt_id' => $attempt->id,
        'question_id' => $essay->id,
        'answer' => 'Jawaban siswa',
        'is_correct' => true,
        'score_awarded' => 9,
    ]);

    $this->actingAs($studentUser)
        ->get(route('student.results'))
        ->assertOk()
        ->assertSee('Belum Diterbitkan')
        ->assertDontSee('90.0');

    $exam->update(['is_published' => true]);

    $this->actingAs($studentUser)
        ->get(route('student.results'))
        ->assertOk()
        ->assertSee('90.0');
});
