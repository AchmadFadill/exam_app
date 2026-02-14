<?php

namespace App\Http\Controllers;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Gate;

class GradingPrintController extends Controller
{
    public function __invoke(Exam $exam)
    {
        Gate::authorize('grade', $exam);

        $targetStatuses = [
            ExamAttemptStatus::Submitted->value,
            ExamAttemptStatus::Graded->value,
            ExamAttemptStatus::Completed->value,
            ExamAttemptStatus::TimedOut->value,
            ExamAttemptStatus::Abandoned->value,
        ];

        $attempts = ExamAttempt::query()
            ->where('exam_id', $exam->id)
            ->whereIn('status', $targetStatuses)
            ->with(['student.user', 'student.classroom'])
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->get();

        return view('teacher.grading.print', [
            'exam' => $exam,
            'attempts' => $attempts,
            'printedAt' => now(),
            'backRoute' => auth()->user()->isAdmin()
                ? route('admin.grading.show', ['exam' => $exam->id])
                : route('teacher.grading.show', ['exam' => $exam->id]),
        ]);
    }
}
