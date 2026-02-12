<?php

use App\Models\Exam;
use App\Models\ExamActivity;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('prunes exam activity logs older than 24 hours', function () {
    $teacherUser = User::create([
        'name' => 'Teacher Prune',
        'email' => 'teacher.prune@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);
    $teacher = Teacher::create(['user_id' => $teacherUser->id]);

    $studentUser = User::create([
        'name' => 'Student Prune',
        'email' => 'student.prune@example.test',
        'password' => Hash::make('password123'),
        'role' => 'student',
    ]);

    $subject = Subject::create([
        'name' => 'Prune Subject',
        'code' => 'PRN-SBJ',
    ]);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Prune Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'PRN001',
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

    $oldLog = ExamActivity::create([
        'user_id' => $studentUser->id,
        'exam_id' => $exam->id,
        'type' => 'tab_switch',
        'severity' => 'warning',
        'message' => 'Old violation log',
    ]);
    $oldLog->timestamps = false;
    $oldLog->created_at = now()->subHours(25);
    $oldLog->updated_at = now()->subHours(25);
    $oldLog->save();

    $recentLog = ExamActivity::create([
        'user_id' => $studentUser->id,
        'exam_id' => $exam->id,
        'type' => 'tab_switch',
        'severity' => 'warning',
        'message' => 'Recent violation log',
    ]);

    $this->artisan('exam:prune-activity-logs --hours=24')
        ->assertSuccessful();

    $this->assertDatabaseMissing('exam_activities', ['id' => $oldLog->id]);
    $this->assertDatabaseHas('exam_activities', ['id' => $recentLog->id]);
});

