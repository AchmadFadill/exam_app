<?php

namespace App\Livewire\Student;

use App\Enums\ExamAttemptStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ExamResults extends Component
{
    use WithPagination;

    public function render()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return view('student.exam.results', ['results' => []])
                ->layout('layouts.student', ['title' => 'Hasil Ujian']);
        }

        // Fetch all completed exam attempts for this student
        $results = \App\Models\ExamAttempt::where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with(['exam.subject'])
            ->latest('submitted_at')
            ->paginate(10)
            ->through(function($attempt) {
                return [
                    'id' => $attempt->id,
                    'subject' => $attempt->exam->subject->name ?? '-',
                    'exam_name' => $attempt->exam->name,
                    'submitted_at' => $attempt->submitted_at->translatedFormat('d M Y, H:i'),
                    'score' => $attempt->percentage ?? 0,
                    'passed' => $attempt->passed,
                    'status' => $attempt->status instanceof ExamAttemptStatus ? $attempt->status->value : $attempt->status,
                    'show_score_to_student' => (bool) ($attempt->exam->show_score_to_student ?? true),
                    'show_answers_to_student' => (bool) ($attempt->exam->show_answers_to_student ?? true),
                ];
            });

        return view('student.exam.results', [
            'results' => $results
        ])->layout('layouts.student', ['title' => 'Hasil Ujian']);
    }
}
