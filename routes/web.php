<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Redirect authenticated users to their dashboard
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => view('landing'),
        };
    }
    return view('landing');
});

// ============================================
// AUTH ROUTES (Guest Only)
// ============================================
Route::middleware('guest')->group(function () {
    // Admin Login
    Route::get('/login', [LoginController::class, 'showAdminLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'adminLogin']);

    // Teacher Login
    Route::get('/teacher/login', [LoginController::class, 'showTeacherLoginForm'])->name('teacher.login');
    Route::post('/teacher/login', [LoginController::class, 'teacherLogin']);

    // Student Login
    Route::get('/student/login', [LoginController::class, 'showStudentLoginForm'])->name('student.login');
    Route::post('/student/login', [LoginController::class, 'studentLogin']);
});

// Logout (Authenticated)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ============================================
// ADMIN ROUTES
// ============================================
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
    Route::get('/teachers', App\Livewire\Admin\ManageTeacher::class)->name('admin.teachers');
    Route::get('/students', App\Livewire\Admin\ManageStudent::class)->name('admin.students');
    Route::get('/classes', App\Livewire\Admin\ManageClass::class)->name('admin.classes');
    Route::get('/subjects', App\Livewire\Admin\ManageSubject::class)->name('admin.subjects');
    Route::get('/exams', App\Livewire\Admin\ManageExam::class)->name('admin.exams');
    Route::get('/monitor', App\Livewire\Common\Monitoring\Index::class)->name('admin.monitor');
    Route::get('/monitor/{id}', App\Livewire\Common\Monitoring\Detail::class)->name('admin.monitor.detail');
    Route::get('/reports', App\Livewire\Common\Report\Index::class)->name('admin.reports.index');
    Route::get('/reports/{id}', App\Livewire\Common\Report\Detail::class)->name('admin.reports.detail');
    Route::get('/settings', App\Livewire\Admin\Settings::class)->name('admin.settings');
});

// ============================================
// TEACHER (GURU) ROUTES
// ============================================
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'guru'])->group(function () {
    Route::get('/dashboard', App\Livewire\Teacher\Dashboard::class)->name('dashboard');
    
    // Question Management (single-page CRUD)
    Route::get('/questions', App\Livewire\Teacher\ManageQuestion::class)->name('questions');
    Route::get('/questions/group/{title}', App\Livewire\Teacher\QuestionGroupDetail::class)->name('questions.group');
    
    // Exam Management
    Route::get('/exams', App\Livewire\Teacher\Exam\Index::class)->name('exams.index');
    Route::get('/exams/create', App\Livewire\Teacher\Exam\Form::class)->name('exams.create');
    Route::get('/exams/{id}/edit', App\Livewire\Teacher\Exam\Form::class)->name('exams.edit');

    // Monitoring & Grading
    Route::get('/monitoring', App\Livewire\Common\Monitoring\Index::class)->name('monitoring');
    Route::get('/monitoring/{id}', App\Livewire\Common\Monitoring\Detail::class)->name('monitoring.detail');
    Route::get('/grading', App\Livewire\Teacher\Grading\Index::class)->name('grading.index');
    Route::get('/grading/{exam}', App\Livewire\Teacher\Grading\StudentList::class)->name('grading.show');
    Route::get('/grading/{exam}/{student}', App\Livewire\Teacher\Grading\Detail::class)->name('grading.detail');
    
    // Reports
    Route::get('/reports', App\Livewire\Common\Report\Index::class)->name('reports.index');
    Route::get('/reports/{id}', App\Livewire\Common\Report\Detail::class)->name('reports.detail');
});

// ============================================
// STUDENT (SISWA) ROUTES
// ============================================
Route::prefix('student')->name('student.')->middleware(['auth', 'siswa'])->group(function () {
    Route::get('/dashboard', App\Livewire\Student\Dashboard::class)->name('dashboard');

    // Exam Management
    Route::get('/exams', App\Livewire\Student\ExamList::class)->name('exams.index');
    Route::get('/exam/{id}/start', App\Livewire\Student\ExamStart::class)->name('exam.start');

    Route::get('/exam/{id}/take', [App\Http\Controllers\Student\ExamController::class, 'show'])->name('exam.show');
    Route::post('/exam/{id}/submit', [App\Http\Controllers\Student\ExamController::class, 'submit'])->name('exam.submit');

    Route::get('/results', App\Livewire\Student\ExamResults::class)->name('results');

    Route::get('/results/{id}', [App\Http\Controllers\Student\ExamController::class, 'result_detail'])->name('results.detail');
});
