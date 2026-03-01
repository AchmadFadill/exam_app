<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ForcePasswordChangeController;
use App\Http\Controllers\GradingPrintController;
use App\Http\Controllers\ReportPrintController;
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

    // Password Reset Request
    Route::get('/student/password-reset', App\Livewire\Auth\StudentPasswordReset::class)->name('student.password-reset');
    Route::get('/teacher/forgot-password', App\Livewire\Auth\TeacherPasswordReset::class)->name('teacher.password-reset');
});

// Logout (Authenticated)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/password/change', [ForcePasswordChangeController::class, 'show'])->name('admin.password.force');
    Route::post('/admin/password/change', [ForcePasswordChangeController::class, 'update'])->name('admin.password.force.update');
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::prefix('admin')->middleware(['auth', 'admin', 'force_admin_password_change'])->group(function () {
    Route::get('/dashboard', App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
    Route::get('/teachers', App\Livewire\Admin\ManageTeacher::class)->name('admin.teachers');
    Route::get('/students', App\Livewire\Admin\ManageStudent::class)->name('admin.students');
    Route::get('/classes', App\Livewire\Admin\ManageClass::class)->name('admin.classes');
    Route::get('/classes/{classroom}/assign', App\Livewire\Admin\AssignClassStudents::class)->name('admin.classes.assign');
    Route::get('/subjects', App\Livewire\Admin\ManageSubject::class)->name('admin.subjects');
    Route::get('/questions', App\Livewire\Teacher\ManageQuestion::class)->name('admin.questions');
    Route::get('/questions/group/{title}', App\Livewire\Teacher\QuestionGroupDetail::class)
        ->where('title', '.*')
        ->name('admin.questions.group');
    Route::get('/exams', App\Livewire\Admin\ManageExam::class)->name('admin.exams');
    Route::get('/exams/create', \App\Livewire\Teacher\Exam\Form::class)->name('admin.exams.create');
    Route::get('/exams/{id}/edit', \App\Livewire\Teacher\Exam\Form::class)->name('admin.exams.edit');
    Route::get('/monitor', App\Livewire\Common\Monitoring\Index::class)->name('admin.monitor');
    // Admin Grading Routes (Alias to Teacher Components)
    Route::get('/grading', \App\Livewire\Teacher\Grading\Index::class)->name('admin.grading.index');
    Route::get('/grading/{exam}', \App\Livewire\Teacher\Grading\StudentList::class)->name('admin.grading.show');
    Route::get('/grading/{exam}/student/{student}', \App\Livewire\Teacher\Grading\Detail::class)->name('admin.grading.detail');
    Route::get('/grading/{exam}/print', GradingPrintController::class)->name('admin.grading.print');
    Route::get('/monitor/{id}', App\Livewire\Common\Monitoring\Detail::class)->name('admin.monitor.detail');
    Route::get('/reports', App\Livewire\Common\Report\Index::class)->name('admin.reports.index');
    Route::get('/reports/{id}', App\Livewire\Common\Report\Detail::class)->name('admin.reports.detail');
    Route::get('/reports/{id}/print', ReportPrintController::class)->name('admin.reports.print');
    Route::get('/reports/{examId}/student/{studentId}', App\Livewire\Common\Report\StudentDetail::class)->name('admin.reports.student');
    Route::get('/reports/{examId}/analysis', App\Livewire\Common\Report\QuestionAnalysis::class)->name('admin.reports.analysis');
    Route::get('/settings', App\Livewire\Admin\Settings::class)->name('admin.settings');
    Route::get('/password-requests', App\Livewire\Admin\PasswordRequests::class)->name('admin.password-requests');
});

// ============================================
// TEACHER (GURU) ROUTES
// ============================================
Route::prefix('teacher')->name('teacher.')->middleware(['auth', 'guru'])->group(function () {
    Route::get('/dashboard', App\Livewire\Teacher\Dashboard::class)->name('dashboard');
    
    // Question Management (single-page CRUD)
    Route::get('/questions', App\Livewire\Teacher\ManageQuestion::class)->name('questions');
    Route::get('/questions/group/{title}', App\Livewire\Teacher\QuestionGroupDetail::class)
        ->where('title', '.*')
        ->name('questions.group');
    
    // Exam Management
    Route::get('/exams', App\Livewire\Teacher\Exam\Index::class)->name('exams.index');
    Route::get('/exams/create', App\Livewire\Teacher\Exam\Form::class)->name('exams.create');
    Route::get('/exams/{id}/edit', App\Livewire\Teacher\Exam\Form::class)->name('exams.edit');

    // Monitoring & Grading
    Route::get('/monitoring', App\Livewire\Common\Monitoring\Index::class)->name('monitoring');
    Route::get('/monitoring/{id}', App\Livewire\Common\Monitoring\Detail::class)->name('monitoring.detail');
    Route::get('/grading', App\Livewire\Teacher\Grading\Index::class)->name('grading.index');
    Route::get('/grading/{exam}', App\Livewire\Teacher\Grading\StudentList::class)->name('grading.show');
    Route::get('/grading/{exam}/print', GradingPrintController::class)->name('grading.print');
    Route::get('/grading/{exam}/{student}', App\Livewire\Teacher\Grading\Detail::class)->name('grading.detail');
    
    // Reports
    Route::get('/reports', App\Livewire\Common\Report\Index::class)->name('reports.index');
    Route::get('/reports/{id}', App\Livewire\Common\Report\Detail::class)->name('reports.detail');
    Route::get('/reports/{id}/print', ReportPrintController::class)->name('reports.print');
    Route::get('/reports/{examId}/student/{studentId}', App\Livewire\Common\Report\StudentDetail::class)->name('reports.student');
    Route::get('/reports/{examId}/analysis', App\Livewire\Common\Report\QuestionAnalysis::class)->name('reports.analysis');
    Route::get('/settings', App\Livewire\Common\ProfileSettings::class)->name('settings');
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
    Route::post('/exam/{id}/log-violation', [App\Http\Controllers\Student\ExamController::class, 'logViolation'])->name('exam.log-violation');
    Route::post('/exam/{id}/save-answer', [App\Http\Controllers\Student\ExamController::class, 'saveAnswer'])->name('exam.save-answer');
    Route::post('/exam/{id}/heartbeat', [App\Http\Controllers\Student\ExamController::class, 'heartbeat'])->name('exam.heartbeat');

    Route::get('/results', App\Livewire\Student\ExamResults::class)->name('results');

    Route::get('/exam/{id}/status', [App\Http\Controllers\Student\ExamController::class, 'statusCheck'])->name('exam.status_check'); // New Route
    
    Route::get('/results/{id}', [App\Http\Controllers\Student\ExamController::class, 'result_detail'])->name('results.detail');
    Route::get('/settings', App\Livewire\Common\ProfileSettings::class)->name('settings');
});
