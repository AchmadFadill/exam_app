<?php

namespace App\Livewire\Teacher\Grading;

use App\Enums\ExamAttemptStatus;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class StudentList extends Component
{
    use \Livewire\WithPagination;

    public $examId;
    public $exam;
    
    // Publish logic (optional for now, maybe toggle exam visibility?)
    public function publish()
    {
        $this->exam->update(['is_published' => true]);
        $this->dispatch('notify', ['message' => 'Nilai ujian berhasil dipublikasikan!']);
    }

    public function mount($exam)
    {
        $this->examId = $exam;
        $this->exam = \App\Models\Exam::findOrFail($exam);
        Gate::authorize('grade', $this->exam);

        // Grading page is only for exams with essay questions.
        $hasEssay = $this->exam->questions()->where('type', 'essay')->exists();
        if (!$hasEssay) {
            $route = auth()->user()->isAdmin() ? 'admin.grading.index' : 'teacher.grading.index';
            return redirect()->route($route);
        }
    }

    public function render()
    {
        $targetStatuses = [
            ExamAttemptStatus::Submitted->value, 
            ExamAttemptStatus::Graded->value,
            ExamAttemptStatus::Completed->value,
            ExamAttemptStatus::TimedOut->value,
            ExamAttemptStatus::Abandoned->value
        ];

        $attempts = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->whereIn('status', $targetStatuses)
            ->with('student.user')
            // Prioritize pending statuses (Submitted, Completed, TimedOut, Abandoned) over Graded
            ->orderByRaw("FIELD(status, ?, ?, ?, ?, ?)", [
                ExamAttemptStatus::Submitted->value, 
                ExamAttemptStatus::Completed->value,
                ExamAttemptStatus::TimedOut->value,
                ExamAttemptStatus::Abandoned->value,
                ExamAttemptStatus::Graded->value
            ])
            ->latest('submitted_at')
            ->paginate(10);

        return view('teacher.grading.student-list', [
            'attempts' => $attempts,
            'examName' => $this->exam->name,
            'className' => $this->exam->class, // Assuming class is a string on Exam, or relation
            'isPublished' => $this->exam->is_published
        ])->layout(\Illuminate\Support\Facades\Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.teacher')->title('Daftar Siswa - ' . $this->exam->name);
    }

}
