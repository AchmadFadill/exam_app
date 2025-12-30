<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Staff (Admin & Guru) Login Route
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/teachers', App\Livewire\Admin\ManageTeacher::class)->name('admin.teachers');
    Route::get('/students', App\Livewire\Admin\ManageStudent::class)->name('admin.students');
    Route::get('/classes', App\Livewire\Admin\ManageClass::class)->name('admin.classes');
    Route::get('/subjects', App\Livewire\Admin\ManageSubject::class)->name('admin.subjects');
    Route::get('/exams', App\Livewire\Admin\ManageExam::class)->name('admin.exams');
    Route::get('/monitor', App\Livewire\Admin\Monitoring\Index::class)->name('admin.monitor');
    Route::get('/monitor/{id}', App\Livewire\Admin\MonitorExam::class)->name('admin.monitor.detail');
    
    // Reports / Hasil Ujian
    Route::get('/reports', App\Livewire\Admin\Reports\Index::class)->name('admin.reports.index');
    Route::get('/reports/{id}', App\Livewire\Admin\Reports\Detail::class)->name('admin.reports.detail');

    Route::get('/settings', App\Livewire\Admin\Settings::class)->name('admin.settings');
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

    Route::get('/exams/{id}/edit', App\Livewire\Teacher\Exam\Form::class)->name('exams.edit');

    // Placeholders for future routes
    Route::get('/monitoring', App\Livewire\Teacher\Monitoring\Index::class)->name('monitoring');
    Route::get('/monitoring/{id}', App\Livewire\Teacher\Exam\Monitor::class)->name('monitoring.detail');
    Route::get('/grading', App\Livewire\Teacher\Grading\Index::class)->name('grading.index');
    Route::get('/grading/{exam}', App\Livewire\Teacher\Grading\StudentList::class)->name('grading.show');
    Route::get('/grading/{exam}/{student}', App\Livewire\Teacher\Grading\Detail::class)->name('grading.detail');
    
    // Reports
    Route::get('/reports', App\Livewire\Teacher\Report\Index::class)->name('reports.index');
    Route::get('/reports/{id}', App\Livewire\Teacher\Report\Detail::class)->name('reports.detail');
});

// Student Routes
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');
    
    Route::get('/login', function () {
        return view('student.auth.login');
    })->name('login');

    Route::get('/exams', function () {
        return view('student.exam.index');
    })->name('exams.index');
    
    Route::get('/exam/{id}/start', function ($id) {
        return view('student.exam.start');
    })->name('exam.start');

    Route::get('/exam/{id}/take', function ($id) {
        return view('student.exam.show');
    })->name('exam.show');

    Route::get('/results', function () {
        return view('student.exam.results');
    })->name('results');

    Route::get('/results/{id}', function ($id) {
        return view('student.exam.result_detail');
    })->name('results.detail');
});
