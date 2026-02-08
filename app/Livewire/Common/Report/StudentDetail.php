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

    public function mount($examId, $studentId)
    {
        $this->exam = \App\Models\Exam::findOrFail($examId);
        Gate::authorize('viewReport', $this->exam);
        $this->student = \App\Models\Student::with('user:id,name')->findOrFail($studentId);
        
        $this->attempt = \App\Models\ExamAttempt::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with(['answers.question.options'])
            ->first();
    }

    public function render()
    {
        $isAdmin = request()->is('admin/*');

        return $this->applyLayout('livewire.common.report.student-detail', [
            'backRoute' => $isAdmin ? 'admin.reports.detail' : 'teacher.reports.detail',
            'backParam' => $this->exam->id
        ]);
    }

}
