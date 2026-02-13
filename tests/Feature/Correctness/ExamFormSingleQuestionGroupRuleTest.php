<?php

use App\Livewire\Teacher\Exam\Form as ExamForm;
use App\Models\Classroom;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('auto-fills subject from assigned teacher subject when creating exam', function () {
    [$teacherUser, $teacher] = makeTeacherUserForExamFormRule('Teacher Rule 0', 'teacher.rule0@example.test');
    $subject = Subject::create(['name' => 'Biologi', 'code' => 'RULE-BIO']);
    $teacher->subjects()->attach($subject->id);

    Livewire::actingAs($teacherUser)
        ->test(ExamForm::class)
        ->assertSet('subject_id', $subject->id);
});

it('switches to the newly selected group when teacher selects a different question group', function () {
    [$teacherUser, $teacher] = makeTeacherUserForExamFormRule('Teacher Rule', 'teacher.rule@example.test');
    $subject = Subject::create(['name' => 'Math', 'code' => 'RULE-MTH']);
    $teacher->subjects()->attach($subject->id);

    $groupAQuestion = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group A',
        'type' => 'multiple_choice',
        'text' => 'Question A1',
        'score' => 10,
    ]);

    $groupBQuestion = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group B',
        'type' => 'multiple_choice',
        'text' => 'Question B1',
        'score' => 10,
    ]);

    Livewire::actingAs($teacherUser)
        ->test(ExamForm::class)
        ->call('toggleQuestionGroup', 'Group A', $subject->id)
        ->assertSet('selectedQuestions', [$groupAQuestion->id])
        ->call('toggleQuestionGroup', 'Group B', $subject->id)
        ->assertSet('selectedQuestions', [$groupBQuestion->id]);
});

it('auto-normalizes mixed question groups into a single group when saving exam', function () {
    [$teacherUser, $teacher] = makeTeacherUserForExamFormRule('Teacher Rule 2', 'teacher.rule2@example.test');
    $subject = Subject::create(['name' => 'Science', 'code' => 'RULE-SCI']);
    $teacher->subjects()->attach($subject->id);
    $classroom = Classroom::create(['name' => 'X-RULE-1', 'level' => 'X']);

    $groupAQuestion = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group A',
        'type' => 'multiple_choice',
        'text' => 'Question A1',
        'score' => 10,
    ]);

    $groupBQuestion = Question::create([
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
        'title' => 'Group B',
        'type' => 'multiple_choice',
        'text' => 'Question B1',
        'score' => 10,
    ]);

    Livewire::actingAs($teacherUser)
        ->test(ExamForm::class)
        ->set('name', 'Ujian Aturan Grup')
        ->set('subject_id', $subject->id)
        ->set('date', now()->toDateString())
        ->set('start_time', '08:00')
        ->set('end_time', '10:00')
        ->set('duration_minutes', 120)
        ->set('passing_grade', 70)
        ->set('default_score', 10)
        ->set('classes', [$classroom->id])
        ->set('token', 'RUL001')
        ->set('tab_tolerance', 3)
        ->set('selectedQuestions', [$groupAQuestion->id, $groupBQuestion->id])
        ->set('questionScores', [
            $groupAQuestion->id => 10,
            $groupBQuestion->id => 10,
        ])
        ->call('saveExam')
        ->assertHasNoErrors()
        ->assertRedirect(route('teacher.exams.index'));

    $exam = \App\Models\Exam::query()->where('token', 'RUL001')->firstOrFail();
    $exam->load('questions:id,title,subject_id');

    $distinctGroups = $exam->questions
        ->map(fn ($q) => $q->subject_id . '::' . $q->title)
        ->unique()
        ->count();

    expect($distinctGroups)->toBe(1);
});

function makeTeacherUserForExamFormRule(string $name, string $email): array
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
