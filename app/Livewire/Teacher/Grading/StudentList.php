<?php

namespace App\Livewire\Teacher\Grading;

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
    }

    public function render()
    {
        $attempts = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->whereIn('status', ['submitted', 'graded'])
            ->with('student.user')
            ->orderByRaw("FIELD(status, 'submitted', 'graded')") // Prioritize submitted
            ->latest('submitted_at')
            ->paginate(10);

        return view('teacher.grading.student-list', [
            'attempts' => $attempts,
            'examName' => $this->exam->name,
            'className' => $this->exam->class, // Assuming class is a string on Exam, or relation
            'isPublished' => $this->exam->is_published
        ])->layout('layouts.teacher')->title('Daftar Siswa - ' . $this->exam->name);
    }
}
