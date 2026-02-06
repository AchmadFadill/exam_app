<?php

namespace App\Livewire\Common\Report;

use Livewire\Component;
use App\Traits\HasDynamicLayout;

class StudentDetail extends Component
{
    use HasDynamicLayout;

    public $exam;
    public $student;
    public $attempt;

    public function mount($examId, $studentId)
    {
        $this->exam = \App\Models\Exam::findOrFail($examId);
        $this->student = \App\Models\Student::findOrFail($studentId);
        
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
