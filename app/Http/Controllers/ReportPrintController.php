<?php

namespace App\Http\Controllers;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ReportPrintController extends Controller
{
    public function __invoke($id)
    {
        $exam = Exam::with(['subject', 'classrooms'])->findOrFail($id);
        Gate::authorize('viewReport', $exam);

        $attemptsByStudent = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->get(['student_id', 'total_score', 'status'])
            ->keyBy('student_id');

        $classIds = $exam->classrooms->pluck('id');
        $students = Student::query()
            ->with(['user:id,name', 'classroom:id,name'])
            ->whereIn('classroom_id', $classIds)
            ->get(['id', 'user_id', 'classroom_id', 'nis'])
            ->map(function (Student $student) use ($attemptsByStudent, $exam) {
                $attempt = $attemptsByStudent->get($student->id);
                $attemptStatus = $attempt?->status instanceof ExamAttemptStatus
                    ? $attempt->status->value
                    : $attempt?->status;

                if (!$attempt) {
                    $status = 'Belum Mengerjakan';
                } elseif ($attemptStatus === ExamAttemptStatus::Graded->value) {
                    $status = ($attempt->total_score ?? 0) >= $exam->passing_grade ? 'Lulus' : 'Tidak Lulus';
                } elseif (in_array($attemptStatus, [
                    ExamAttemptStatus::Submitted->value,
                    ExamAttemptStatus::Completed->value,
                    ExamAttemptStatus::TimedOut->value,
                    ExamAttemptStatus::Abandoned->value,
                ], true)) {
                    $status = 'Pending Penilaian';
                } else {
                    $status = 'Sedang Mengerjakan';
                }

                return [
                    'nis' => $student->nis ?: '-',
                    'name' => $student->user->name ?? '-',
                    'class' => $student->classroom->name ?? '-',
                    'score' => $attempt?->total_score,
                    'status' => $status,
                ];
            })
            ->sortBy('name')
            ->values();

        $isAdmin = request()->is('admin/*');
        $adminUser = User::query()->where('role', 'admin')->orderBy('id')->first();
        $schoolName = Setting::getValue('school_name', 'Sekolah CBT');
        $schoolLogo = Setting::getValue('school_logo');

        return view('livewire.common.report.print', [
            'exam' => $exam,
            'rows' => $students,
            'printedAt' => now(),
            'schoolName' => $schoolName,
            'adminName' => $adminUser?->name ?? 'Admin',
            'adminProfileUrl' => $adminUser?->profile_photo_url,
            'schoolLogoUrl' => $schoolLogo ? asset('storage/' . $schoolLogo) : asset('img/logo_school.jpg'),
            'backRoute' => $isAdmin
                ? route('admin.reports.detail', ['id' => $exam->id])
                : route('teacher.reports.detail', ['id' => $exam->id]),
        ]);
    }
}
