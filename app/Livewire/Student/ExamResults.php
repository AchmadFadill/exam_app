<?php

namespace App\Livewire\Student;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ExamResults extends Component
{
    use WithPagination;
    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $student = Auth::user()->student;
        $finalizedStatuses = array_map(
            static fn (ExamAttemptStatus $status): string => $status->value,
            ExamAttemptStatus::finalized()
        );
        
        if (!$student) {
            return view('student.exam.results', ['results' => []])
                ->layout('layouts.student', ['title' => 'Hasil Ujian']);
        }

        // Fetch all completed exam attempts for this student
        $results = \App\Models\ExamAttempt::where('student_id', $student->id)
            ->where(function ($query) use ($finalizedStatuses) {
                $query->whereNotNull('submitted_at')
                    ->orWhereIn('status', $finalizedStatuses);
            })
            ->when(filled($this->search), function ($query) {
                $keyword = trim((string) $this->search);

                $query->whereHas('exam', function ($examQuery) use ($keyword) {
                    $examQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhereHas('subject', function ($subjectQuery) use ($keyword) {
                            $subjectQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->with([
                'exam.subject',
                'exam.questions:id,type',
                'answers:id,exam_attempt_id,question_id,is_correct',
            ])
            ->latest(\Illuminate\Support\Facades\DB::raw('COALESCE(submitted_at, updated_at)'))
            ->paginate(10)
            ->through(function($attempt) {
                $essayQuestionIds = $attempt->exam->questions
                    ->where('type', 'essay')
                    ->pluck('id')
                    ->values();

                $gradedEssayCount = $attempt->answers
                    ->whereIn('question_id', $essayQuestionIds)
                    ->whereNotNull('is_correct')
                    ->count();

                $hasPendingEssay = $essayQuestionIds->isNotEmpty()
                    && $gradedEssayCount < $essayQuestionIds->count();

                return [
                    'id' => $attempt->id,
                    'subject' => $attempt->exam->subject->name ?? '-',
                    'exam_name' => $attempt->exam->name,
                    'submitted_at' => $attempt->submitted_at->translatedFormat('d M Y, H:i'),
                    'score' => $attempt->percentage ?? 0,
                    'passed' => $attempt->passed,
                    'status' => $attempt->status instanceof ExamAttemptStatus ? $attempt->status->value : $attempt->status,
                    'is_published' => (bool) ($attempt->exam->is_published ?? false),
                    'show_score_to_student' => (bool) ($attempt->exam->show_score_to_student ?? true),
                    'show_answers_to_student' => (bool) ($attempt->exam->show_answers_to_student ?? true),
                    'has_pending_essay' => $hasPendingEssay,
                ];
            });

        return view('student.exam.results', [
            'results' => $results
        ])->layout('layouts.student', ['title' => 'Hasil Ujian']);
    }
}
