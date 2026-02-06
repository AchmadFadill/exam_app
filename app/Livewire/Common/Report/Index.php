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
            ->with(['subject', 'classrooms', 'attempts' => function($q) {
                $q->whereNotNull('submitted_at');
            }]);

        // Teacher only sees their own exams
        if (!$isAdmin && $user->isTeacher()) {
            $query->where('teacher_id', $user->teacher->id ?? 0);
        }

        $exams = $query->latest('date')->paginate(10);

        // Transform collection for view
        $results = $exams->getCollection()->map(function ($exam) {
            $submittedAttempts = $exam->attempts;
            
            return [
                'id' => $exam->id,
                'exam_name' => $exam->name,
                'class' => $exam->classrooms->pluck('name')->join(', '),
                'subject' => $exam->subject->name ?? '-',
                'date' => $exam->date ? $exam->date->format('d M Y') : '-',
                'participants' => $submittedAttempts->count(),
                'avg_score' => $submittedAttempts->count() > 0 
                    ? number_format($submittedAttempts->avg('total_score'), 1) 
                    : 0
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
