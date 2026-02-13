<?php

use App\Livewire\Teacher\Question\QuestionForm;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('forces teacher question subject to assigned subject when payload is outside assignment', function () {
    [$teacherUser, $teacher] = makeTeacherWithSubjectsForQuestionLock('Teacher Lock 1', 'teacher.lock1@example.test');
    $allowedSubject = Subject::create(['name' => 'Kimia', 'code' => 'LOCK-KIM']);
    $otherSubject = Subject::create(['name' => 'Geografi', 'code' => 'LOCK-GEO']);
    $teacher->subjects()->attach($allowedSubject->id);

    Livewire::actingAs($teacherUser)
        ->test(QuestionForm::class)
        ->set('questionForm.title', 'Grup Lock')
        ->set('questionForm.subject_id', $otherSubject->id)
        ->set('questionForm.type', 'multiple_choice')
        ->set('questionForm.text', 'Soal lock')
        ->set('questionForm.score', 10)
        ->set('questionForm.options.0', 'A1')
        ->set('questionForm.options.1', 'B1')
        ->set('questionForm.options.2', 'C1')
        ->set('questionForm.options.3', 'D1')
        ->set('questionForm.options.4', 'E1')
        ->set('questionForm.correct_option', 'A')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('questions', [
        'teacher_id' => $teacher->id,
        'subject_id' => $otherSubject->id,
        'title' => 'Grup Lock',
    ]);

    $this->assertDatabaseHas('questions', [
        'teacher_id' => $teacher->id,
        'subject_id' => $allowedSubject->id,
        'title' => 'Grup Lock',
    ]);
});

it('allows teacher to save question to assigned subject', function () {
    [$teacherUser, $teacher] = makeTeacherWithSubjectsForQuestionLock('Teacher Lock 2', 'teacher.lock2@example.test');
    $allowedSubject = Subject::create(['name' => 'Biologi', 'code' => 'LOCK-BIO']);
    $teacher->subjects()->attach($allowedSubject->id);

    Livewire::actingAs($teacherUser)
        ->test(QuestionForm::class)
        ->set('questionForm.title', 'Grup Lock 2')
        ->set('questionForm.subject_id', $allowedSubject->id)
        ->set('questionForm.type', 'multiple_choice')
        ->set('questionForm.text', 'Soal valid')
        ->set('questionForm.score', 10)
        ->set('questionForm.options.0', 'A1')
        ->set('questionForm.options.1', 'B1')
        ->set('questionForm.options.2', 'C1')
        ->set('questionForm.options.3', 'D1')
        ->set('questionForm.options.4', 'E1')
        ->set('questionForm.correct_option', 'A')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('questions', [
        'teacher_id' => $teacher->id,
        'subject_id' => $allowedSubject->id,
        'title' => 'Grup Lock 2',
    ]);
});

function makeTeacherWithSubjectsForQuestionLock(string $name, string $email): array
{
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ]);

    $teacher = Teacher::create(['user_id' => $user->id]);

    return [$user, $teacher];
}
