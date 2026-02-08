<?php

use App\Models\Exam;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('grants admin full exam permissions', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin.policy@example.test',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    [, $teacher] = makeTeacherUserForExamPolicy('Teacher Owner', 'teacher.owner.policy@example.test');
    $subject = Subject::create(['name' => 'Math', 'code' => 'POLMATH']);

    $exam = Exam::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'name' => 'Policy Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'POL001',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    expect($admin->can('view', $exam))->toBeTrue()
        ->and($admin->can('update', $exam))->toBeTrue()
        ->and($admin->can('delete', $exam))->toBeTrue()
        ->and($admin->can('grade', $exam))->toBeTrue()
        ->and($admin->can('viewReport', $exam))->toBeTrue();
});

it('grants teacher only on owned exam and denies non-owned exam', function () {
    [$ownerUser, $ownerTeacher] = makeTeacherUserForExamPolicy('Teacher Owner', 'teacher.owner2.policy@example.test');
    [$otherUser, $otherTeacher] = makeTeacherUserForExamPolicy('Teacher Other', 'teacher.other.policy@example.test');
    $subject = Subject::create(['name' => 'Science', 'code' => 'POLSCI']);

    $ownedExam = Exam::create([
        'teacher_id' => $ownerTeacher->id,
        'subject_id' => $subject->id,
        'name' => 'Owned Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'POL002',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    $otherExam = Exam::create([
        'teacher_id' => $otherTeacher->id,
        'subject_id' => $subject->id,
        'name' => 'Other Exam',
        'date' => now()->toDateString(),
        'start_time' => '08:00',
        'end_time' => '10:00',
        'duration_minutes' => 120,
        'token' => 'POL003',
        'passing_grade' => 70,
        'default_score' => 10,
        'shuffle_questions' => false,
        'shuffle_answers' => false,
        'enable_tab_tolerance' => false,
        'tab_tolerance' => 3,
        'status' => 'scheduled',
    ]);

    foreach (['view', 'update', 'delete', 'grade', 'viewReport'] as $ability) {
        expect($ownerUser->can($ability, $ownedExam))->toBeTrue();
        expect($ownerUser->can($ability, $otherExam))->toBeFalse();
    }
});

function makeTeacherUserForExamPolicy(string $name, string $email): array
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
