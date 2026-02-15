<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Gate;

class StudentDetail extends Component
{
    use HasDynamicLayout;

    public $exam;
    public $student;
    public $attempt;
    public $from = 'report';

    public function mount($examId, $studentId)
    {
        $this->exam = \App\Models\Exam::with(['questions.options'])->findOrFail($examId);
        Gate::authorize('viewReport', $this->exam);
        $this->student = \App\Models\Student::with('user:id,name')->findOrFail($studentId);
        $this->from = request()->query('from', 'report');
        
        $this->attempt = \App\Models\ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with(['answers'])
            ->first();
    }

    public function render()
    {
        $isAdmin = request()->is('admin/*');
        $fromMonitoring = $this->from === 'monitoring';

        return $this->applyLayout('livewire.common.report.student-detail', [
            'backRoute' => $fromMonitoring
                ? ($isAdmin ? 'admin.monitor.detail' : 'teacher.monitoring.detail')
                : ($isAdmin ? 'admin.reports.detail' : 'teacher.reports.detail'),
            'backParam' => $this->exam->id
        ]);
    }

}
