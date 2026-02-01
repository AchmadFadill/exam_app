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
        return $this->exam->questions;
    }

    public function getCurrentQuestionProperty()
    {
        return $this->questions[$this->currentQuestionIndex];
    }

    public function loadQuestionState()
    {
        // Load existing answer for current question
        $answer = \App\Models\StudentAnswer::where('exam_attempt_id', $this->attemptId)
            ->where('question_id', $this->currentQuestion->id)
            ->first();
            
        if ($answer) {
            $this->selectedOption = $answer->selected_option;
            $this->essayAnswer = $answer->answer;
        } else {
            $this->selectedOption = null;
            $this->essayAnswer = '';
        }
    }

    public function saveAnswer()
    {
        $question = $this->currentQuestion;
        
        $data = [
            'exam_attempt_id' => $this->attemptId,
            'question_id' => $question->id,
        ];

        if ($question->type === 'multiple_choice') {
            $data['selected_option'] = $this->selectedOption;
            
            // Auto-grade MC
            $correctOption = $question->options->where('is_correct', true)->first();
            $isCorrect = $correctOption && $correctOption->label === $this->selectedOption;
            
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
        $this->saveAnswer(); // Save last question
        
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

        return redirect()->route('student.exams.index')->with('success', 'Ujian berhasil dikumpulkan!');
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
