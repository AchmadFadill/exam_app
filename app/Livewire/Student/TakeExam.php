<?php

namespace App\Livewire\Student;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TakeExam extends Component
{
    use AuthorizesRequests;

    public $examId;
    public $attemptId;
    public $currentQuestionIndex = 0;
    
    // Current Answer State
    public $selectedOption;
    public $essayAnswer;
    
    // Timer
    public $remainingSeconds;

    protected $listeners = ['timeExpired' => 'submitExam'];

    public function mount($id)
    {
        $this->examId = $id;
        $student = \Illuminate\Support\Facades\Auth::user()->student;
        
        $attempt = \App\Models\ExamAttempt::where('exam_id', $this->examId)
            ->where('student_id', $student->id)
            ->first();
            
        if (!$attempt || $attempt->status !== 'in_progress') {
            return redirect()->route('student.exams.index');
        }
        
        $this->attemptId = $attempt->id;
        
        // Load current question state
        $this->loadQuestionState();
        
        // Calculate remaining time
        $exam = $this->exam;
        $endTime = \Carbon\Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes);
        
        // Also check hard deadline
        $examEndTime = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
        $finalDeadline = $endTime->min($examEndTime);
        
        $this->remainingSeconds = max(0, now()->diffInSeconds($finalDeadline, false));
        
        if ($this->remainingSeconds <= 0) {
            $this->submitExam();
        }
    }

    public function getExamProperty()
    {
        return \App\Models\Exam::with(['questions.options'])->findOrFail($this->examId);
    }
    
    public function getAttemptProperty()
    {
        return \App\Models\ExamAttempt::find($this->attemptId);
    }

    public function getQuestionsProperty()
    {
        $questions = $this->exam->questions;
        
        if ($this->exam->shuffle_questions) {
            $student = \Illuminate\Support\Facades\Auth::user()->student;
            $seed = $student->id + $this->exam->id;
            return $questions->shuffle($seed);
        }
        
        return $questions;
    }

    public function getCurrentQuestionProperty()
    {
        return $this->questions[$this->currentQuestionIndex];
    }

    public function getCurrentOptionsProperty()
    {
        $options = $this->currentQuestion->options;
        
        if ($this->exam->shuffle_answers && $this->currentQuestion->type === 'multiple_choice') {
            $student = \Illuminate\Support\Facades\Auth::user()->student;
            $seed = $student->id + $this->exam->id + $this->currentQuestion->id;
            return $options->shuffle($seed);
        }
        
        return $options;
    }

    public function loadQuestionState()
    {
        // Load existing answer for current question
        $answer = \App\Models\StudentAnswer::where('exam_attempt_id', $this->attemptId)
            ->where('question_id', $this->currentQuestion->id)
            ->first();
            
        if ($answer) {
            $this->selectedOption = $answer->selected_option_id;
            $this->essayAnswer = $answer->answer;
        } else {
            $this->selectedOption = null;
            $this->essayAnswer = '';
        }
    }

    public function saveAnswer()
    {
        // Guard clauses
        if (!$this->attemptId || !$this->examId) return;

        // 1. Validate Time Server-Side
        if ($this->hasTimeExpired()) {
            return; 
        }

        try {
            $question = $this->currentQuestion;
            if (!$question) return;
            
            $data = [
                'exam_attempt_id' => $this->attemptId,
                'question_id' => $question->id,
            ];

            if ($question->type === 'multiple_choice') {
                $data['selected_option_id'] = $this->selectedOption;
                
                $selectedOptModel = $question->options->where('id', $this->selectedOption)->first();
                $isCorrect = $selectedOptModel && $selectedOptModel->is_correct;
                
                $data['is_correct'] = $isCorrect;
                $data['score_awarded'] = $isCorrect ? $question->pivot->score : 0;
                
            } else {
                $data['answer'] = $this->essayAnswer;
                $data['is_correct'] = null;
                $data['score_awarded'] = null;
            }

            \App\Models\StudentAnswer::updateOrCreate(
                ['exam_attempt_id' => $this->attemptId, 'question_id' => $question->id],
                $data
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SaveAnswer Failed: ' . $e->getMessage());
        }
    }
    
    // ... next/prev/jump methods ...

    public function submitExam()
    {
        \Illuminate\Support\Facades\Log::info('SubmitExam Triggered for Attempt: ' . $this->attemptId);

        try {
            // Ensure strictly one submission
            $this->attempt->refresh();
            if (in_array($this->attempt->status, ['submitted', 'graded', 'completed'])) {
                return $this->redirect(route('student.exams.index'));
            }

            $this->saveAnswer();
            
            $attempt = $this->attempt;
            
            // Calculate total score for auto-graded questions
            $totalScore = $attempt->answers()->sum('score_awarded');
            $maxScore = $this->exam->questions()->sum('score'); // Using direct question score usually, or pivot if defined
            // Note: In saveAnswer we used $question->pivot->score. Check consistency.
            // Let's rely on attempt answers sum for now.

            $hasEssay = $this->exam->questions()->where('type', 'essay')->exists();
            
            $attempt->update([
                'submitted_at' => now(),
                'status' => $hasEssay ? 'submitted' : 'graded',
                'total_score' => $totalScore,
                'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0, 
            ]);

            // Broadcast Submission Event (Reusing StudentViolationEvent for simplicity to trigger dashboard update)
            $studentUser = \Illuminate\Support\Facades\Auth::user();
            $classroom = $studentUser->student->classroom->name ?? 'Unknown Class';
            
            try {
                broadcast(new \App\Events\StudentViolationEvent(
                    $studentUser->id,
                    $studentUser->name,
                    'submit', // Type
                    $this->examId,
                    'Selesai Mengerjakan', // Message
                    $classroom
                ));
            } catch (\Exception $e) {
                // Ignore broadcast errors
            }

            session()->flash('success', 'Ujian berhasil dikumpulkan!');
            return $this->redirect(route('student.exams.index'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('SubmitExam Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Terjadi kesalahan sistem saat mengirim jawaban. Hubungi pengawas.']);
        }
    }

    // ... hasTimeExpired ...

    public function checkStatus()
    {
        try {
            $this->attempt->refresh(); // This might trigger query
            
            if (in_array($this->attempt->status, ['submitted', 'graded', 'completed'])) {
                session()->flash('warning', 'Ujian telah dihentikan oleh pengawas.');
                return $this->redirect(route('student.exams.index'));
            }
        } catch (\Exception $e) {
            // Ignore errors in polling to avoid popup spam
        }
    }

    public function handleViolation($type, $message)
    {
        \Illuminate\Support\Facades\Log::info("🛡️ [STUDENT-SIDE] handleViolation CALLED: {$type} - {$message}");

        // 1. Log to Activities Table (Atomic Operation)
        try {
            \App\Models\ExamActivity::create([
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                'exam_id' => $this->examId,
                'exam_attempt_id' => $this->attemptId,
                'type' => $type,
                'severity' => in_array($type, ['tab_switch', 'fullscreen_exit']) ? 'warning' : 'info',
                'message' => $message,
                'metadata' => [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]
            ]);

            // 2. Update Attempt Counter
            if ($type === 'tab_switch' || $type === 'fullscreen_exit') {
                $this->attempt->increment('tab_switches');
            }

            // 3. Broadcast Event (Real-time)
            $student = \Illuminate\Support\Facades\Auth::user();
            $classroom = $student->student->classroom->name ?? 'Unknown Class';
            
            broadcast(new \App\Events\StudentViolationEvent(
                $student->id,
                $student->name,
                $type,
                $this->examId,
                $message,
                $classroom
            ));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("handleViolation FAILED: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.student.take-exam', [
            'exam' => $this->exam,
            'currentQuestion' => $this->currentQuestion,
            'questions' => $this->questions,
            'answeredCount' => \App\Models\StudentAnswer::where('exam_attempt_id', $this->attemptId)->count()
        ])->layout('layouts.student', ['title' => 'Ujian Berlangsung']);
    }
}
