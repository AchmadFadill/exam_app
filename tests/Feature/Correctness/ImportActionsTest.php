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

it('updates existing student when student nis already exists', function () {
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

    $newClassroom = Classroom::create(['name' => 'XI IPA 1', 'level' => 'XI']);

    $result = app(ImportStudentRowAction::class)->execute([
        'nis' => 'NIS-DUP-001',
        'nama' => 'Siswa Duplikat',
        'email' => 'existing.student@example.test',
        'kelas' => '11 ipa-1',
    ], 8);

    expect($result['status'])->toBe('imported');

    $student = Student::where('nis', 'NIS-DUP-001')->firstOrFail();
    $student->load('user');

    expect($student->user->name)->toBe('Siswa Duplikat')
        ->and($student->classroom_id)->toBe($newClassroom->id);
});

it('supports student import header aliases for name and class columns', function () {
    $classroom = Classroom::create(['name' => 'X IPS 1', 'level' => 'X']);

    $result = app(ImportStudentRowAction::class)->execute([
        'nis' => 'NIS-ALIAS-001',
        'name' => 'Siswa Alias',
        'email' => 'siswa.alias@example.test',
        'classroom' => '10 ips 1',
    ], 12);

    expect($result['status'])->toBe('imported');

    $user = User::where('email', 'siswa.alias@example.test')->firstOrFail();
    $student = Student::where('user_id', $user->id)->firstOrFail();

    expect($student->classroom_id)->toBe($classroom->id);
});
