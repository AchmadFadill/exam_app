<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use HasDynamicLayout;
    use \Livewire\WithPagination;

    /**
     * @return array<int, string>
     */
    private function finalizedStatuses(): array
    {
        return \array_map(
            static fn (\App\Enums\ExamAttemptStatus $status): string => $status->value,
            \App\Enums\ExamAttemptStatus::finalized()
        );
    }

    private function applyReportEligibility($query): void
    {
        $query->where(function ($sub) {
            $sub->whereNotNull('submitted_at')
                ->orWhereIn('status', $this->finalizedStatuses())
                ->orWhereHas('answers');
        });
    }

    public function render()
    {
        $isAdmin = request()->is('admin/*');
        $user = auth()->user();

        $query = \App\Models\Exam::query()
            ->with(['subject', 'classrooms', 'teacher.user'])
            ->withCount([
                'attempts as participants_count' => function ($q) {
                    $this->applyReportEligibility($q);
                },
            ])
            ->withAvg([
                'attempts as avg_total_score' => function ($q) {
                    $this->applyReportEligibility($q);
                },
            ], 'total_score');

        if ($isAdmin) {
            // Admin reports should still list historical exams even when attempt
            // rows are partially inconsistent after data recovery operations.
            $query->where('status', '!=', 'draft');
        } else {
            $query->where(function ($q) {
                $q->whereHas('attempts', function ($attempts) {
                    $this->applyReportEligibility($attempts);
                })
                ->orWhere('is_published', true);
            });
        }

        // Teacher only sees their own exams.
        if (!$isAdmin && $user->isTeacher()) {
            $query->where('teacher_id', $user->teacher->id ?? 0);
        }

        $exams = $query->latest('date')->paginate(10);

        // Transform collection for view
        $results = $exams->getCollection()->map(function ($exam) {
            return [
                'id' => $exam->id,
                'exam_name' => $exam->name,
                'teacher_name' => $exam->teacher?->user?->name ?? '-',
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'subject' => $exam->subject->name ?? '-',
                'date' => $exam->date ? $exam->date->format('d M Y') : '-',
                'participants' => (int) ($exam->participants_count ?? 0),
                'avg_score' => $exam->participants_count > 0
                    ? number_format((float) ($exam->avg_total_score ?? 0), 1)
                    : 0,
            ];
        });

        // Re-set the collection to the paginator instance to preserve pagination links
        $exams->setCollection($results);

        return $this->applyLayout('livewire.common.report.index', [
            'results' => $exams, // Passing paginator
            'detailRoute' => $isAdmin ? 'admin.reports.detail' : 'teacher.reports.detail'
        ]);
    }
}
