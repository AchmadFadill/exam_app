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
        // 1. Validate Time Server-Side
        if ($this->hasTimeExpired()) {
            return; // Silently fail or dispatch error
        }

        $question = $this->currentQuestion;
        
        $data = [
            'exam_attempt_id' => $this->attemptId,
            'question_id' => $question->id,
        ];

        if ($question->type === 'multiple_choice') {
            $data['selected_option_id'] = $this->selectedOption;
            
            // Auto-grade MC
            // Verify the selected option exists and check if it's correct
            $selectedOptModel = $question->options->where('id', $this->selectedOption)->first();
            $isCorrect = $selectedOptModel && $selectedOptModel->is_correct;
            
            $data['is_correct'] = $isCorrect;
            $data['score_awarded'] = $isCorrect ? $question->pivot->score : 0;
            
        } else {
            $data['answer'] = $this->essayAnswer;
            // Essay needs manual grading
            $data['is_correct'] = null;
            $data['score_awarded'] = null;
        }

        \App\Models\StudentAnswer::updateOrCreate(
            ['exam_attempt_id' => $this->attemptId, 'question_id' => $question->id],
            $data
        );
    }

    public function nextQuestion()
    {
        $this->saveAnswer();
        if ($this->currentQuestionIndex < $this->questions->count() - 1) {
            $this->currentQuestionIndex++;
            $this->loadQuestionState();
        }
    }

    public function prevQuestion()
    {
        $this->saveAnswer();
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->loadQuestionState();
        }
    }
    
    public function jumpToQuestion($index)
    {
        $this->saveAnswer();
        if ($index >= 0 && $index < $this->questions->count()) {
            $this->currentQuestionIndex = $index;
            $this->loadQuestionState();
        }
    }

    public function submitExam()
    {
        // Ensure strictly one submission
        if ($this->attempt->status === 'submitted' || $this->attempt->status === 'graded') {
            return $this->redirect(route('student.exams.index'));
        }

        $this->saveAnswer(); // Save last question (will be blocked if time expired)
        
        $attempt = $this->attempt;
        
        // Calculate total score for auto-graded questions
        $totalScore = $attempt->answers->sum('score_awarded');
        $maxScore = $this->exam->questions->sum('pivot.score');
        
        // If all questions are MC, we can finalize grade immediately
        // For mixed/essay, status might be 'submitted' waiting for grading
        
        $hasEssay = $this->exam->questions->where('type', 'essay')->isNotEmpty();
        
        $attempt->update([
            'submitted_at' => now(),
            'status' => $hasEssay ? 'submitted' : 'graded',
            'total_score' => $totalScore,
            // Simple percentage calc, can be refined
            'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0, 
        ]);

        session()->flash('success', 'Ujian berhasil dikumpulkan!');
        return $this->redirect(route('student.exams.index'));
    }

    /**
     * Server-side check if time has expired
     * Allows a 2-minute buffer for network latency
     */
    protected function hasTimeExpired()
    {
        $exam = $this->exam;
        $attempt = $this->attempt;
        
        $startedAt = \Carbon\Carbon::parse($attempt->started_at);
        $allowedDuration = $exam->duration_minutes;
        
        // Theoretical end time
        $endTime = $startedAt->copy()->addMinutes($allowedDuration);
        
        // Hard deadline (Exam End Time)
        $examHardDeadline = \Carbon\Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
        
        // The actual limit is the earliest of the two
        $limit = $endTime->min($examHardDeadline);
        
        // Buffer: 60 seconds tolerance
        if (now()->gt($limit->addSeconds(60))) {
            return true;
        }
        
        return false;
    }

    public function render()
    {
        return view('livewire.student.take-exam', [
            'exam' => $this->exam,
            'currentQuestion' => $this->currentQuestion,
            'questions' => $this->questions,
            'answeredCount' => \App\Models\StudentAnswer::where('exam_attempt_id', $this->attemptId)->count()
        ])->layout('layouts.student', ['title' => 'Ujian Berlangsung']); // Use a dedicated layout later if needed
    }
}
