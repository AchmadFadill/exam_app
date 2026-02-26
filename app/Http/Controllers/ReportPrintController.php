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
        $sessionClassroom = (string) session('report_classroom_exam_' . $exam->id, '');
        $classroomFilter = (int) request()->query('classroomFilter', $sessionClassroom !== '' ? (int) $sessionClassroom : 0);

        $sessionSort = (string) session('report_sort_exam_' . $exam->id, 'default');
        $sortBy = strtolower(trim((string) request()->query('sortBy', $sessionSort ?: 'default')));
        if (!in_array($sortBy, ['default', 'highest', 'lowest', 'fastest', 'slowest'], true)) {
            $sortBy = 'default';
        }
        $selectedClassroom = $classroomFilter > 0
            ? $exam->classrooms->firstWhere('id', $classroomFilter)
            : null;

        $attemptsByStudent = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->withCount('answers')
            ->withSum('answers', 'score_awarded')
            ->get(['student_id', 'total_score', 'status'])
            ->keyBy('student_id');

        $essayQuestionCount = $exam->questions()->where('type', 'essay')->count();
        $examEndAt = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
        $examWindowEnded = now()->gt($examEndAt);

        $classIds = $exam->classrooms->pluck('id');
        $students = Student::query()
            ->with(['user:id,name', 'classroom:id,name'])
            ->whereIn('classroom_id', $classIds)
            ->when($classroomFilter > 0, function ($query) use ($classroomFilter) {
                $query->where('classroom_id', $classroomFilter);
            })
            ->get(['id', 'user_id', 'classroom_id', 'nis'])
            ->map(function (Student $student) use ($attemptsByStudent, $exam, $essayQuestionCount, $examWindowEnded) {
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
                    $status = $essayQuestionCount > 0
                        ? 'Pending Penilaian'
                        : (($attempt->total_score ?? 0) >= $exam->passing_grade ? 'Lulus' : 'Tidak Lulus');
                } elseif (in_array($attemptStatus, [
                    ExamAttemptStatus::InProgress->value,
                    ExamAttemptStatus::Ongoing->value,
                ], true)) {
                    $hasNoAnswer = (int) ($attempt->answers_count ?? 0) === 0;
                    if ($hasNoAnswer) {
                        $status = $examWindowEnded ? 'Tidak Mengerjakan' : 'Belum Mengerjakan';
                    } else {
                        $status = $examWindowEnded ? 'Waktu Habis' : 'Sedang Mengerjakan';
                    }
                } else {
                    $status = 'Tidak Lulus';
                }

                $resolvedScore = null;
                if ($attempt) {
                    if (!is_null($attempt->total_score)) {
                        $resolvedScore = (float) $attempt->total_score;
                    } elseif ((int) ($attempt->answers_count ?? 0) > 0) {
                        $resolvedScore = (float) ($attempt->answers_sum_score_awarded ?? 0);
                    }
                }

                return [
                    'nis' => $student->nis ?: '-',
                    'name' => $student->user->name ?? '-',
                    'class' => $student->classroom->name ?? '-',
                    'score' => $resolvedScore,
                    'status' => $status,
                    'duration_minutes' => ($attempt && $attempt->started_at && $attempt->submitted_at)
                        ? $attempt->submitted_at->diffInMinutes($attempt->started_at)
                        : 999999,
                    'has_attempt' => (bool) $attempt,
                ];
            });

        if ($sortBy === 'highest') {
            $students = $students->sort(function (array $a, array $b): int {
                $scoreA = $a['score'] ?? null;
                $scoreB = $b['score'] ?? null;
                if ($scoreA === null && $scoreB === null) return 0;
                if ($scoreA === null) return 1;
                if ($scoreB === null) return -1;
                return (float) $scoreB <=> (float) $scoreA;
            })->values();
        } elseif ($sortBy === 'lowest') {
            $students = $students->sort(function (array $a, array $b): int {
                $scoreA = $a['score'] ?? null;
                $scoreB = $b['score'] ?? null;
                if ($scoreA === null && $scoreB === null) return 0;
                if ($scoreA === null) return 1;
                if ($scoreB === null) return -1;
                return (float) $scoreA <=> (float) $scoreB;
            })->values();
        } elseif ($sortBy === 'fastest') {
            $students = $students->sort(function (array $a, array $b): int {
                if (!$a['has_attempt'] && !$b['has_attempt']) return 0;
                if (!$a['has_attempt']) return 1;
                if (!$b['has_attempt']) return -1;
                return (int) $a['duration_minutes'] <=> (int) $b['duration_minutes'];
            })->values();
        } elseif ($sortBy === 'slowest') {
            $students = $students->sort(function (array $a, array $b): int {
                if (!$a['has_attempt'] && !$b['has_attempt']) return 0;
                if (!$a['has_attempt']) return 1;
                if (!$b['has_attempt']) return -1;
                return (int) $b['duration_minutes'] <=> (int) $a['duration_minutes'];
            })->values();
        } else {
            $students = $students->sortBy('name')->values();
        }

        $students = $students->map(function (array $row): array {
            unset($row['duration_minutes'], $row['has_attempt']);
            return $row;
        })->values();

        $isAdmin = request()->is('admin/*');
        $adminUser = User::query()->where('role', 'admin')->orderBy('id')->first();
        $schoolName = Setting::getValue('school_name', 'Sekolah CBT');
        $schoolLogo = Setting::getValue('school_logo');
        $semester = Setting::getValue('semester', '-');
        $academicYear = Setting::getValue('academic_year', '-');
        $classLabel = $selectedClassroom
            ? $selectedClassroom->name
            : $exam->classrooms->pluck('name')->join(', ');

        return view('livewire.common.report.print', [
            'exam' => $exam,
            'rows' => $students,
            'printedAt' => now(),
            'schoolName' => $schoolName,
            'adminName' => $adminUser?->name ?? 'Admin',
            'adminProfileUrl' => $adminUser?->profile_photo_url,
            'schoolLogoUrl' => $schoolLogo ? asset('storage/' . $schoolLogo) : asset('img/logo_school.jpg'),
            'subjectName' => $exam->subject->name ?? '-',
            'classSemester' => trim(($classLabel ?: '-') . ' - ' . ($semester ?: '-')),
            'academicYear' => $academicYear ?: '-',
            'backRoute' => $isAdmin
                ? route('admin.reports.detail', ['id' => $exam->id, 'classroomFilter' => $classroomFilter > 0 ? $classroomFilter : null])
                : route('teacher.reports.detail', ['id' => $exam->id, 'classroomFilter' => $classroomFilter > 0 ? $classroomFilter : null]),
        ]);
    }
}
