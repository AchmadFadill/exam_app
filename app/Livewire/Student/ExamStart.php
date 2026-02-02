<?php

namespace App\Livewire\Student;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamStart extends Component
{
    use AuthorizesRequests;

    public $examId;
    public $token = '';
    
    public function mount($id)
    {
        $this->examId = $id;
        
        $exam = $this->exam;
        if (!$exam) abort(404);

        // If attempt exists and is in progress, redirect to take exam
        // If submitted, redirect to results (future)
        $attempt = \App\Models\ExamAttempt::where('exam_id', $id)
            ->where('student_id', \Illuminate\Support\Facades\Auth::user()->student->id)
            ->first();
            
        if ($attempt) {
            if ($attempt->status === 'in_progress') {
                return redirect()->route('student.exam.show', $id);
            }
            // If submitted/graded, maybe show result later
        }
    }
    
    public function getExamProperty()
    {
        return \App\Models\Exam::with(['subject', 'teacher.user', 'questions'])
            ->findOrFail($this->examId);
    }
    
    public function startExam()
    {
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        
        // Validate: time window
        if (!$this->canStart()) {
            $this->dispatch('notify', [
                'message' => 'Ujian belum dimulai atau sudah berakhir.',
                'type' => 'error'
            ]);
            return;
        }

        // Validate Token if exam has one
        if ($this->exam->token && trim(strtoupper($this->token)) !== trim(strtoupper($this->exam->token))) {
            $this->dispatch('notify', [
                'message' => 'Token ujian tidak valid. Pastikan token yang dimasukkan benar.',
                'type' => 'error'
            ]);
            return;
        }
        
        // Create attempt
        try {
            \App\Models\ExamAttempt::create([
                'exam_id' => $this->examId,
                'student_id' => $student->id,
                'started_at' => now(),
                'status' => 'in_progress'
            ]);
            
            return redirect()->route('student.exam.show', $this->examId);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'message' => 'Gagal memulai ujian: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }
    
    public function canStart() {
        $exam = $this->exam;
        
        // Check time window
        return $exam->status === 'scheduled'
            && $exam->date->isToday()
            && now()->between(\Carbon\Carbon::parse($exam->start_time), \Carbon\Carbon::parse($exam->end_time));
    }
    
    public function render()
    {
        return view('livewire.student.exam-start', [
            'exam' => $this->exam
        ])->layout('layouts.student', ['title' => 'Persiapan Ujian']);
    }
}
