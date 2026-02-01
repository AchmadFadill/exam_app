<?php

namespace App\Livewire\Common\Monitoring;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use HasDynamicLayout;

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $isAdmin = $user->isAdmin();
        
        // Base query for active exams
        $query = \App\Models\Exam::where('status', 'scheduled')
            ->whereDate('date', now())
            ->whereTime('start_time', '<=', now()->format('H:i'))
            ->whereTime('end_time', '>=', now()->format('H:i'));
            
        // Filter by teacher if not admin
        if (!$isAdmin) {
            $query->where('teacher_id', $user->teacher->id);
        }
        
        $activeExams = $query->with(['classrooms', 'attempts'])
            ->get()
            ->map(function($exam) {
                $totalStudents = $exam->classrooms->sum(function($c) { 
                    return $c->students()->count(); 
                });
                
                $attempts = $exam->attempts;
                
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'class' => $exam->classrooms->pluck('name')->join(', '),
                    'subject' => $exam->subject->name,
                    'start_time' => \Carbon\Carbon::parse($exam->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($exam->end_time)->format('H:i'),
                    'total_students' => $totalStudents,
                    'working' => $attempts->where('status', 'in_progress')->count(),
                    'finished' => $attempts->whereIn('status', ['submitted', 'graded'])->count(),
                    'not_started' => $totalStudents - $attempts->count(),
                ];
            });

        return $this->applyLayout('livewire.common.monitoring.index', [
            'activeExams' => $activeExams,
            'detailRoute' => $isAdmin ? 'admin.monitor.detail' : 'teacher.monitoring.detail'
        ]);
    }
}
