<?php

namespace App\Livewire\Teacher\Grading;

use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;

    public function render()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $teacherId = $user->isTeacher() ? $user->teacher->id : 0;

        $exams = \App\Models\Exam::where('teacher_id', $teacherId)
            ->withCount(['attempts' => function ($query) {
                $query->where('status', 'submitted');
            }])
            ->with(['questions' => function ($query) {
                $query->select('questions.id', 'questions.type')->where('questions.type', 'essay');
            }])
            ->latest('date')
            ->paginate(10);

        // Transform collection to add 'pending_count' and 'status' logic
        $exams->getCollection()->transform(function ($exam) {
            $hasEssays = $exam->questions->where('type', 'essay')->count() > 0;
            
            // If exam has essays, pending count is number of submitted attempts (waiting for manual grade)
            // If exam is pure PG, pending count is 0 (auto-graded on submit)
            $exam->pending_count = $hasEssays ? $exam->attempts_count : 0;
            
            $exam->grading_status = ($exam->pending_count > 0) ? 'needs_grading' : 'graded';
            
            return $exam;
        });

        return view('teacher.grading.index', [
            'exams' => $exams
        ])->layout('layouts.teacher')->title('Analisis Nilai');
    }
}
