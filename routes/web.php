<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/teachers', App\Livewire\Admin\ManageTeacher::class)->name('admin.teachers');
});

// Teacher Routes
Route::prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', App\Livewire\Teacher\Dashboard::class)->name('dashboard');
    
    // Question Bank
    Route::get('/question-bank', App\Livewire\Teacher\QuestionBank\Index::class)->name('question-bank.index');
    Route::get('/question-bank/create', App\Livewire\Teacher\QuestionBank\Form::class)->name('question-bank.create');
    Route::get('/question-bank/{id}/edit', App\Livewire\Teacher\QuestionBank\Form::class)->name('question-bank.edit');

    // Exam Management
    Route::get('/exams', App\Livewire\Teacher\Exam\Index::class)->name('exams.index');
    Route::get('/exams/create', App\Livewire\Teacher\Exam\Form::class)->name('exams.create');
    Route::get('/exams/{id}/edit', App\Livewire\Teacher\Exam\Form::class)->name('exams.edit');

    // Placeholders for future routes
    Route::get('/monitoring', App\Livewire\Teacher\Exam\Monitor::class)->name('monitoring');
    Route::get('/grading', App\Livewire\Teacher\Grading\Index::class)->name('grading.index');
    Route::get('/grading/{exam}/{student}', App\Livewire\Teacher\Grading\Detail::class)->name('grading.detail');
    Route::get('/reports', App\Livewire\Teacher\Report\Index::class)->name('reports.index');
});

// Student Routes
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');
    
    Route::get('/exams', function () {
        return view('student.exam.index');
    })->name('exams.index');
    
    Route::get('/exam/{id}', function ($id) {
        return view('student.exam.show');
    })->name('exam.show');

    Route::get('/results', function () {
        return view('student.exam.results');
    })->name('results');

    Route::get('/results/{id}', function ($id) {
        return view('student.exam.result_detail');
    })->name('results.detail');
});
