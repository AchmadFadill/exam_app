<?php

namespace App\Livewire\Teacher\Grading;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = $user->isAdmin();
        $teacherId = $user->isTeacher() ? $user->teacher->id : 0;

        $targetStatuses = [
            ExamAttemptStatus::Submitted->value,
            ExamAttemptStatus::Completed->value,
            ExamAttemptStatus::TimedOut->value,
            ExamAttemptStatus::Abandoned->value
        ];

        $exams = \App\Models\Exam::query()
            ->when(!$isAdmin, fn ($q) => $q->where('teacher_id', $teacherId))
            // Grading module is only for exams that contain essay questions (manual scoring).
            ->whereHas('questions', fn ($q) => $q->where('questions.type', 'essay'))
            ->withCount([
                'attempts as attempts_count' => function ($query) use ($targetStatuses) {
                    $query->whereIn('status', $targetStatuses);
                },
                'attempts as total_attempts_count'
            ])
            ->with(['questions' => function ($query) {
                $query->select('questions.id', 'questions.type')->where('questions.type', 'essay');
            }])
            // Sort by newest exam record first on grading index.
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        // Transform collection to add 'pending_count' and 'status' logic
        $exams->getCollection()->transform(function ($exam) {
            $hasEssays = $exam->questions->where('type', 'essay')->count() > 0;
            
            // If exam has essays, pending count is number of submitted attempts (waiting for manual grade)
            // If exam is pure PG, pending count is 0 (auto-graded on submit)
            $exam->pending_count = $hasEssays ? $exam->attempts_count : 0;
            
            if ($exam->total_attempts_count === 0) {
                $exam->grading_status = 'no_participants';
            } else {
                $exam->grading_status = ($exam->pending_count > 0) ? 'needs_grading' : 'graded';
            }
            
            return $exam;
        });

        return view('teacher.grading.index', [
            'exams' => $exams
        ])->layout($isAdmin ? 'layouts.admin' : 'layouts.teacher')->title('Analisis Nilai');
    }
}
