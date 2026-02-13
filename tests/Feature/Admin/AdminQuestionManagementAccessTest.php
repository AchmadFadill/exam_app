<?php

use App\Models\Question;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('allows admin to access question management page', function () {
    $admin = User::create([
        'name' => 'Admin Question',
        'email' => 'admin.question@example.test',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    Subject::create([
        'name' => 'Matematika',
        'code' => 'ADM-MATH',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.questions'))
        ->assertOk()
        ->assertSee('Bank Soal');
});

it('allows admin to access question group detail page', function () {
    $admin = User::create([
        'name' => 'Admin Group',
        'email' => 'admin.group@example.test',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $teacherUser = User::create([
        'name' => 'Teacher Group',
        'email' => 'teacher.group@example.test',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);

    $teacher = Teacher::create(['user_id' => $teacherUser->id]);
    $subject = Subject::create(['name' => 'Fisika', 'code' => 'ADM-PHY']);

    Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Grup Admin',
        'type' => 'essay',
        'text' => 'Apa itu gaya?',
        'score' => 10,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.questions.group', ['title' => urlencode('Grup Admin')]))
        ->assertOk()
        ->assertSee('Grup Admin');
});
