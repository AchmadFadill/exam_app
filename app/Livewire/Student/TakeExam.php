<?php

namespace App\Livewire\Student;

use App\Actions\Exam\ProcessExamSubmissionAction;
use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamActivity;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

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

    private ScoringService $scoringService;
    private ProcessExamSubmissionAction $processExamSubmissionAction;

    protected $listeners = ['timeExpired' => 'submitExam'];

    public function boot(
        ScoringService $scoringService,
        ProcessExamSubmissionAction $processExamSubmissionAction
    ): void
    {
        $this->scoringService = $scoringService;
        $this->processExamSubmissionAction = $processExamSubmissionAction;
    }

    public function mount($id)
    {
        $this->examId = $id;
        $student = Auth::user()->student;

        $attempt = ExamAttempt::where('exam_id', $this->examId)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt || $attempt->status !== ExamAttemptStatus::InProgress) {
            return redirect()->route('student.exams.index');
        }

        $this->attemptId = $attempt->id;

        // Load current question state
        $this->loadQuestionState();

        // Calculate remaining time
        $finalDeadline = $this->resolveFinalDeadline($attempt, $this->exam);
        $this->remainingSeconds = max(0, now()->diffInSeconds($finalDeadline, false));

        if ($this->remainingSeconds <= 0) {
            return $this->submitExam();
        }
    }

    public function getExamProperty()
    {
        $exam = Exam::with('examQuestions')->findOrFail($this->examId);

        $orderedQuestionIds = $exam->examQuestions
            ->sortBy('order')
            ->pluck('question_id')
            ->values();

        $questions = Question::query()
            ->with('options')
            ->whereIn('id', $orderedQuestionIds)
            ->get()
            ->keyBy('id');

        $orderedQuestions = $orderedQuestionIds
            ->map(fn (int $questionId) => $questions->get($questionId))
            ->filter()
            ->values();

        $exam->setRelation('questions', $orderedQuestions);

        return $exam;
    }

    public function getAttemptProperty()
    {
        return $this->resolveOwnedAttempt();
    }

    public function getQuestionsProperty()
    {
        $questions = $this->exam->questions;

        if ($this->exam->shuffle_questions) {
            $student = Auth::user()->student;
            $seed = $student->id + $this->exam->id;

            return $questions->shuffle($seed);
        }

        return $questions;
    }

    public function getCurrentQuestionProperty()
    {
        $totalQuestions = $this->questions->count();
        $index = max(0, min((int) $this->currentQuestionIndex, max($totalQuestions - 1, 0)));
        $this->currentQuestionIndex = $index;

        return $this->questions[$index];
    }

    public function getCurrentOptionsProperty()
    {
        $options = $this->currentQuestion->options;

        if ($this->exam->shuffle_answers && $this->currentQuestion->type === 'multiple_choice') {
            $student = Auth::user()->student;
            $seed = $student->id + $this->exam->id + $this->currentQuestion->id;

            return $options->shuffle($seed);
        }

        return $options;
    }

    public function loadQuestionState()
    {
        if (!$this->attempt || !$this->currentQuestion) {
            $this->selectedOption = null;
            $this->essayAnswer = '';

            return;
        }

        // Load existing answer for current question
        $answer = StudentAnswer::where('exam_attempt_id', $this->attemptId)
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
        try {
            $this->persistCurrentAnswer();
        } catch (\Exception $e) {
            Log::error('SaveAnswer Failed: ' . $e->getMessage());
        }
    }

    public function nextQuestion()
    {
        $this->saveAnswer();

        $lastIndex = max($this->questions->count() - 1, 0);
        $this->currentQuestionIndex = min((int) $this->currentQuestionIndex + 1, $lastIndex);
        $this->loadQuestionState();
    }

    public function prevQuestion()
    {
        $this->saveAnswer();

        $this->currentQuestionIndex = max((int) $this->currentQuestionIndex - 1, 0);
        $this->loadQuestionState();
    }

    public function jumpToQuestion(int $index)
    {
        $this->saveAnswer();

        $lastIndex = max($this->questions->count() - 1, 0);
        $this->currentQuestionIndex = max(0, min($index, $lastIndex));
        $this->loadQuestionState();
    }

    public function submitExam()
    {
        Log::info('SubmitExam Triggered for Attempt: ' . $this->attemptId);

        try {
            $attempt = $this->resolveOwnedAttempt();
            if (!$attempt) {
                return $this->redirect(route('student.exams.index'));
            }

            // Ensure strictly one submission
            $attempt->refresh();
            if ($this->isAttemptFinalized($attempt->status)) {
                return $this->redirect(route('student.exams.index'));
            }

            $this->processExamSubmissionAction->execute(
                $this->exam,
                $attempt,
                [],
                function (ExamAttempt $lockedAttempt): void {
                    // On final submit (including timeout), persist current answer before scoring.
                    $this->persistCurrentAnswer(false, $lockedAttempt);
                }
            );

            // Broadcast submission event for dashboard update
            $studentUser = Auth::user();
            $classroom = $studentUser->student->classroom->name ?? 'Unknown Class';

            try {
                broadcast(new \App\Events\StudentViolationEvent(
                    $studentUser->id,
                    $studentUser->name,
                    'submit',
                    $this->examId,
                    'Selesai Mengerjakan',
                    $classroom
                ));
            } catch (\Exception $e) {
                // Ignore broadcast errors
            }

            session()->flash('success', 'Ujian berhasil dikumpulkan!');

            return $this->redirect(route('student.exams.index'));
        } catch (\Exception $e) {
            Log::error('SubmitExam Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Terjadi kesalahan sistem saat mengirim jawaban. Hubungi pengawas.']);
        }
    }

    private function hasTimeExpired(): bool
    {
        $attempt = $this->resolveOwnedAttempt();
        if (!$attempt) {
            return true;
        }

        $deadline = $this->resolveFinalDeadline($attempt, $this->exam);

        return now()->greaterThan($deadline);
    }

    private function resolveFinalDeadline(ExamAttempt $attempt, Exam $exam): Carbon
    {
        $endByDuration = Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes);
        $examEndTime = Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);

        return $endByDuration->min($examEndTime);
    }

    private function isAttemptFinalized(ExamAttemptStatus|string|null $status): bool
    {
        $status = $status instanceof ExamAttemptStatus
            ? $status
            : ($status ? ExamAttemptStatus::tryFrom($status) : null);

        return $status?->isFinalized() ?? false;
    }

    private function resolveOwnedAttempt(): ?ExamAttempt
    {
        $studentId = Auth::user()?->student?->id;
        if (!$studentId || !$this->attemptId || !$this->examId) {
            return null;
        }

        return ExamAttempt::query()
            ->whereKey($this->attemptId)
            ->where('exam_id', $this->examId)
            ->where('student_id', $studentId)
            ->first();
    }

    private function persistCurrentAnswer(bool $enforceTime = true, ?ExamAttempt $attempt = null): void
    {
        $attempt = $attempt ?? $this->resolveOwnedAttempt();
        if (!$attempt) {
            return;
        }

        if ($enforceTime && $this->hasTimeExpired()) {
            return;
        }

        $question = $this->currentQuestion;
        if (!$question || !$this->questions->contains('id', $question->id)) {
            return;
        }

        $answerValue = $question->type === 'multiple_choice'
            ? $this->selectedOption
            : $this->essayAnswer;

        $scored = $this->scoringService->scoreSingleAnswer($this->exam, $question, $answerValue);

        StudentAnswer::updateOrCreate(
            ['exam_attempt_id' => $attempt->id, 'question_id' => $question->id],
            $scored
        );
    }

    public function checkStatus()
    {
        try {
            if (!$this->attempt) {
                return;
            }

            $this->attempt->refresh();

            if ($this->attempt->status === ExamAttemptStatus::InProgress) {
                $this->attempt->forceFill(['last_seen_at' => now()])->save();
            }

            if ($this->isAttemptFinalized($this->attempt->status)) {
                session()->flash('warning', 'Ujian telah dihentikan oleh pengawas.');

                return $this->redirect(route('student.exams.index'));
            }
        } catch (\Exception $e) {
            // Ignore polling errors to avoid popup spam
        }
    }

    public function handleViolation($type, $message)
    {
        Log::info("[STUDENT-SIDE] handleViolation CALLED: {$type} - {$message}");

        // 1. Log to activities table
        try {
            ExamActivity::create([
                'user_id' => Auth::id(),
                'exam_id' => $this->examId,
                'exam_attempt_id' => $this->attemptId,
                'type' => $type,
                'severity' => in_array($type, ['tab_switch', 'fullscreen_exit']) ? 'warning' : 'info',
                'message' => $message,
                'metadata' => [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            // 2. Update attempt counter
            if ($type === 'tab_switch' || $type === 'fullscreen_exit') {
                $this->attempt?->increment('tab_switches');
            }

            // 3. Broadcast event (real-time)
            $student = Auth::user();
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
            Log::error('handleViolation FAILED: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.student.take-exam', [
            'exam' => $this->exam,
            'currentQuestion' => $this->currentQuestion,
            'questions' => $this->questions,
            'answeredCount' => StudentAnswer::where('exam_attempt_id', $this->attemptId)->count(),
        ])->layout('layouts.student', ['title' => 'Ujian Berlangsung']);
    }
}
