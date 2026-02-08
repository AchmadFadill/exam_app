<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use HasDynamicLayout;
    use \Livewire\WithPagination;

    public function render()
    {
        $isAdmin = request()->is('admin/*');
        $user = auth()->user();

        $query = \App\Models\Exam::query()
            ->with(['subject', 'classrooms'])
            ->withCount([
                'attempts as participants_count' => function ($q) {
                    $q->whereNotNull('submitted_at');
                },
            ])
            ->withAvg([
                'attempts as avg_total_score' => function ($q) {
                    $q->whereNotNull('submitted_at');
                },
            ], 'total_score');

        // Teacher only sees their own exams
        if (!$isAdmin && $user->isTeacher()) {
            $query->where('teacher_id', $user->teacher->id ?? 0);
        }

        $exams = $query->latest('date')->paginate(10);

        // Transform collection for view
        $results = $exams->getCollection()->map(function ($exam) {
            return [
                'id' => $exam->id,
                'exam_name' => $exam->name,
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
