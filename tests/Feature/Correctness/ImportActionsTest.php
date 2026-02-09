<?php

use App\Actions\Import\ImportStudentRowAction;
use App\Actions\Import\ImportTeacherRowAction;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('imports a teacher row and assigns subject by code', function () {
    Subject::create(['name' => 'Matematika', 'code' => 'MATH']);

    $result = app(ImportTeacherRowAction::class)->execute([
        'nama' => 'Guru Aksi',
        'email' => 'guru.aksi@example.test',
        'mata_pelajaran' => 'MATH',
    ], 2);

    expect($result['status'])->toBe('imported');

    $user = User::where('email', 'guru.aksi@example.test')->firstOrFail();
    $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('subject_teacher', [
        'teacher_id' => $teacher->id,
    ]);
});

it('returns error when teacher email already exists', function () {
    User::create([
        'name' => 'Existing',
        'email' => 'dup.teacher@example.test',
        'password' => bcrypt('password123'),
        'role' => 'teacher',
    ]);

    $result = app(ImportTeacherRowAction::class)->execute([
        'nama' => 'Guru Duplikat',
        'email' => 'dup.teacher@example.test',
    ], 5);

    expect($result['status'])->toBe('error')
        ->and($result['message'])->toContain('sudah terdaftar');
});

it('imports a student row with generated email when email is empty', function () {
    $classroom = Classroom::create(['name' => 'X-ACT-1', 'level' => 'X']);

    $result = app(ImportStudentRowAction::class)->execute([
        'nis' => 'NIS-ACT-001',
        'nama' => 'Siswa Aksi',
        'email' => '',
        'kelas' => $classroom->name,
    ], 2);

    expect($result['status'])->toBe('imported');

    $user = User::where('email', 'NIS-ACT-001@student.local')->firstOrFail();
    $student = Student::where('user_id', $user->id)->firstOrFail();

    expect($student->classroom_id)->toBe($classroom->id);
});

it('returns skipped for fully empty student row', function () {
    $result = app(ImportStudentRowAction::class)->execute([
        'nis' => '',
        'nama' => '',
        'email' => '',
        'kelas' => '',
    ], 3);

    expect($result['status'])->toBe('skipped');
});

it('returns error when student nis already exists', function () {
    $user = User::create([
        'name' => 'Existing Student',
        'email' => 'existing.student@example.test',
        'password' => bcrypt('password123'),
        'role' => 'student',
    ]);
    Student::create([
        'user_id' => $user->id,
        'nis' => 'NIS-DUP-001',
        'classroom_id' => null,
    ]);

    $result = app(ImportStudentRowAction::class)->execute([
        'nis' => 'NIS-DUP-001',
        'nama' => 'Siswa Duplikat',
        'email' => 'new.student@example.test',
    ], 8);

    expect($result['status'])->toBe('error')
        ->and($result['message'])->toContain('NIS NIS-DUP-001 sudah terdaftar');
});
