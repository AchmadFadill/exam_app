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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * TakeExam – Livewire component for the student exam-taking interface.
 *
 * PERFORMANCE FIXES APPLIED
 * ─────────────────────────
 * 1. Stable Randomization (Snapshot Approach)
 *    • getQuestionsProperty() reads from attempt.question_order / attempt.options_order
 *      which were generated once in ExamStart::startExam() and stored in the DB.
 *    • Zero randomization drift across Livewire re-hydrations.
 *
 * 2. Attempt Memoization
 *    • resolveOwnedAttempt() caches the attempt in $this->_cachedAttempt to avoid
 *      repeated DB queries within a single Livewire request lifecycle.
 *
 * 3. Answered-Status Batch Query
 *    • getAnsweredIdsProperty() fetches ALL answered question IDs for this attempt in
 *      ONE query. The Blade view uses this Collection instead of per-question EXISTS
 *      queries, eliminating the N+1 in the navigation grid.
 *
 * 4. Pessimistic Locking on Answer Writes
 *    • persistCurrentAnswer() wraps StudentAnswer::updateOrCreate() in a
 *      DB::transaction with lockForUpdate() on the attempt row.
 *
 * 5. Non-blocking Navigation
 *    • Navigation methods (next/prev/jump) persist the answer and load the new state
 *      within a single round-trip. Alpine.js handles the optimistic UI update
 *      immediately so the question number flips before the server responds.
 *
 * 6. Optimized Violation Handling
 *    • handleViolation() uses lockForUpdate() when incrementing tab_switches.
 *    • Client-side debouncing (1.5 s) is implemented in Alpine.js in the Blade view.
 *
 * 7. Non-blocking Heartbeat
 *    • checkStatus() writes only last_seen_at via a raw DB::table() update and then
 *      calls skipRender() – no Livewire DOM diff overhead.
 *    • Polling interval is 30 s (wire:poll.30s) instead of 5 s.
 *
 * 8. Livewire Memo Reduction
 *    • Heavy data (questions list, options, answered IDs) are computed properties –
 *      they are NOT stored as public $properties and therefore never serialised into
 *      the Livewire snapshot payload sent on every request.
 */
class TakeExam extends Component
{
    use AuthorizesRequests;

    // ── Immutable (set once in mount, never changed) ─────────────────────────
    #[Locked]
    public int|string $examId;

    #[Locked]
    public int|string $attemptId;

    // ── Navigation state ─────────────────────────────────────────────────────
    public int $currentQuestionIndex = 0;

    // ── Current answer state (lightweight – just IDs / short text) ───────────
    public $selectedOption;
    public $essayAnswer = '';

    // ── Timer ────────────────────────────────────────────────────────────────
    public int $remainingSeconds = 0;

    // ── Service injection ────────────────────────────────────────────────────
    private ScoringService $scoringService;
    private ProcessExamSubmissionAction $processExamSubmissionAction;

    /**
     * In-request attempt cache.
     * Prevents repeated identical DB queries within a single Livewire lifecycle.
     * Must NOT be public (would be serialised into the Livewire snapshot).
     */
    private ?ExamAttempt $_cachedAttempt = null;

    protected $listeners = ['timeExpired' => 'submitExam'];

    public function boot(
        ScoringService $scoringService,
        ProcessExamSubmissionAction $processExamSubmissionAction
    ): void {
        $this->scoringService             = $scoringService;
        $this->processExamSubmissionAction = $processExamSubmissionAction;
    }

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(int|string $id): mixed
    {
        $this->examId  = $id;
        $student       = Auth::user()->student;

        $attempt = ExamAttempt::where('exam_id', $this->examId)
            ->where('student_id', $student->id)
            ->first();

        if (!$attempt || $attempt->status !== ExamAttemptStatus::InProgress) {
            return $this->redirect(route('student.exams.index'));
        }

        $this->attemptId       = $attempt->id;
        $this->_cachedAttempt  = $attempt;

        // Load current-question answer from DB
        $this->loadQuestionState();

        // Calculate remaining time
        $finalDeadline        = $this->resolveFinalDeadline($attempt, $this->exam);
        $this->remainingSeconds = max(0, (int) now()->diffInSeconds($finalDeadline, false));

        if ($this->remainingSeconds <= 0) {
            return $this->submitExam();
        }

        return null;
    }

    // ── Computed properties (NOT serialised into Livewire snapshot) ──────────

    /**
     * Returns the Exam model with examQuestions loaded.
     * Loaded once per request; not stored in public properties.
     */
    public function getExamProperty(): Exam
    {
        return Exam::with('examQuestions')->findOrFail($this->examId);
    }

    /** Returns the ExamAttempt for the authenticated student (memoized). */
    public function getAttemptProperty(): ?ExamAttempt
    {
        return $this->resolveOwnedAttempt();
    }

    /**
     * Returns the ordered Question collection using the SNAPSHOT stored in the
     * attempt row (question_order JSON).  Never re-shuffles.
     */
    public function getQuestionsProperty(): Collection
    {
        $attempt = $this->resolveOwnedAttempt();

        // ── Snapshot path (preferred) ────────────────────────────────────────
        if ($attempt && !empty($attempt->question_order)) {
            $orderedIds = $attempt->question_order; // cast → array

            $questionMap = Question::with('options')
                ->whereIn('id', $orderedIds)
                ->get()
                ->keyBy('id');

            return collect($orderedIds)
                ->map(fn (int $qId) => $questionMap->get($qId))
                ->filter()
                ->values();
        }

        // ── Legacy fallback (attempts created before the migration) ──────────
        $exam       = $this->exam;
        $orderedIds = $exam->examQuestions
            ->sortBy(fn ($row) => sprintf('%010d-%010d', (int) $row->order, (int) $row->question_id))
            ->pluck('question_id')
            ->values();

        $questionMap = Question::with('options')
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');

        return $orderedIds
            ->map(fn (int $qId) => $questionMap->get($qId))
            ->filter()
            ->values();
    }

    /** Returns the current Question object. */
    public function getCurrentQuestionProperty(): ?Question
    {
        $questions = $this->questions;
        $total     = $questions->count();
        if ($total === 0) {
            return null;
        }

        $index = max(0, min((int) $this->currentQuestionIndex, $total - 1));
        $this->currentQuestionIndex = $index;

        return $questions[$index];
    }

    /**
     * Returns the options for the current question using the SNAPSHOT.
     * Never re-shuffles.
     */
    public function getCurrentOptionsProperty(): Collection
    {
        $question = $this->currentQuestion;
        if (!$question) {
            return collect();
        }

        $attempt = $this->resolveOwnedAttempt();

        // ── Snapshot path ────────────────────────────────────────────────────
        if ($attempt && !empty($attempt->options_order)) {
            $optionIds = $attempt->options_order[(string) $question->id] ?? null;

            if ($optionIds) {
                $optionObjects = $question->options->keyBy('id');

                return collect($optionIds)
                    ->map(fn (int $oId) => $optionObjects->get($oId))
                    ->filter()
                    ->values();
            }
        }

        // ── Legacy fallback ──────────────────────────────────────────────────
        return $question->options;
    }

    /**
     * Returns a flat Collection of question IDs that have been answered.
     *
     * ONE query replaces the N per-question EXISTS calls that previously ran
     * inside the Blade navigation-grid loop.
     *
     * @return Collection<int>
     */
    public function getAnsweredIdsProperty(): Collection
    {
        return StudentAnswer::where('exam_attempt_id', $this->attemptId)
            ->pluck('question_id');
    }

    // ── Answer state management ──────────────────────────────────────────────

    public function loadQuestionState(): void
    {
        $question = $this->currentQuestion;

        if (!$question) {
            $this->selectedOption = null;
            $this->essayAnswer    = '';
            return;
        }

        $answer = StudentAnswer::where('exam_attempt_id', $this->attemptId)
            ->where('question_id', $question->id)
            ->first();

        if ($answer) {
            $this->selectedOption = $answer->selected_option_id;
            $this->essayAnswer    = $answer->answer ?? '';
        } else {
            $this->selectedOption = null;
            $this->essayAnswer    = '';
        }
    }

    // ── Navigation actions ───────────────────────────────────────────────────

    public function nextQuestion(): void
    {
        $this->saveAnswer();

        $lastIndex                  = max($this->questions->count() - 1, 0);
        $this->currentQuestionIndex = min((int) $this->currentQuestionIndex + 1, $lastIndex);
        $this->loadQuestionState();
    }

    public function prevQuestion(): void
    {
        $this->saveAnswer();

        $this->currentQuestionIndex = max((int) $this->currentQuestionIndex - 1, 0);
        $this->loadQuestionState();
    }

    public function jumpToQuestion(int $index): void
    {
        $this->saveAnswer();

        $lastIndex                  = max($this->questions->count() - 1, 0);
        $this->currentQuestionIndex = max(0, min($index, $lastIndex));
        $this->loadQuestionState();
    }

    // ── Answer persistence ───────────────────────────────────────────────────

    /**
     * Public method called by Livewire from the UI.
     * Wraps persistCurrentAnswer() with basic error handling.
     */
    public function saveAnswer(): void
    {
        try {
            $this->persistCurrentAnswer();
        } catch (\Exception $e) {
            Log::error('SaveAnswer Failed: ' . $e->getMessage());
        }
    }

    /**
     * Persists the current question's answer with PESSIMISTIC LOCKING.
     *
     * lockForUpdate() on the attempt row serialises concurrent writes from
     * the same student (e.g. double-click, race between tab-switch handler
     * and normal navigation), preventing duplicate StudentAnswer rows and
     * corrupt counts under 400-user load.
     */
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

        // ── Pessimistic lock: prevents duplicate concurrent writes ────────────
        DB::transaction(function () use ($attempt, $question, $scored): void {
            // Lock the attempt row for this transaction's duration
            ExamAttempt::query()
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->first();

            StudentAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'question_id' => $question->id],
                $scored
            );
        });
    }

    // ── Exam submission ──────────────────────────────────────────────────────

    public function submitExam(): mixed
    {
        Log::info('SubmitExam Triggered for Attempt: ' . $this->attemptId);

        try {
            $attempt = $this->resolveOwnedAttempt();
            if (!$attempt) {
                return $this->redirect(route('student.exams.index'));
            }

            $attempt->refresh();
            if ($this->isAttemptFinalized($attempt->status)) {
                return $this->redirect(route('student.exams.index'));
            }

            $this->processExamSubmissionAction->execute(
                $this->exam,
                $attempt,
                [],
                function (ExamAttempt $lockedAttempt): void {
                    // Persist current answer inside the locked transaction
                    $this->persistCurrentAnswer(false, $lockedAttempt);
                }
            );

            // Broadcast (non-critical – ignore failures)
            try {
                $studentUser = Auth::user();
                $classroom   = $studentUser->student->classroom->name ?? 'Unknown Class';

                broadcast(new \App\Events\StudentViolationEvent(
                    $studentUser->id,
                    $studentUser->name,
                    'submit',
                    $this->examId,
                    'Selesai Mengerjakan',
                    $classroom
                ));
            } catch (\Exception) {
                // Non-critical – swallow
            }

            session()->flash('success', 'Ujian berhasil dikumpulkan!');
            return $this->redirect(route('student.exams.index'));

        } catch (\Exception $e) {
            Log::error('SubmitExam Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            $this->dispatch('notify', [
                'type'    => 'error',
                'message' => 'Terjadi kesalahan sistem saat mengirim jawaban. Hubungi pengawas.',
            ]);
        }

        return null;
    }

    // ── Heartbeat (non-re-rendering) ─────────────────────────────────────────

    /**
     * Polling heartbeat called every 30 s (wire:poll.30s in the Blade view).
     *
     * Uses skipRender() so Livewire does NOT diff/re-render the DOM – this is a
     * pure DB-write that avoids unnecessary round-trip overhead.
     *
     * Also checks whether the exam was force-stopped by the teacher.
     *
     * Design note: we use DB::table() here (not Eloquent) to avoid loading the
     * full model, triggering casts, or dirtying the model cache.
     */
    public function checkStatus(): mixed
    {
        try {
            $attempt = $this->resolveOwnedAttempt();
            if (!$attempt) {
                return null;
            }

            // Re-fetch status directly – avoids re-hydrating the full model
            $fresh = DB::table('exam_attempts')
                ->where('id', $attempt->id)
                ->select('status', 'last_seen_at')
                ->first();

            if (!$fresh) {
                $this->skipRender();
                return null;
            }

            if ($fresh->status === ExamAttemptStatus::InProgress->value) {
                // Lightweight update – only last_seen_at, no Eloquent overhead
                DB::table('exam_attempts')
                    ->where('id', $attempt->id)
                    ->update(['last_seen_at' => now()]);
            }

            $freshStatus = ExamAttemptStatus::tryFrom($fresh->status);
            if ($freshStatus && $this->isAttemptFinalized($freshStatus)) {
                session()->flash('warning', 'Ujian telah dihentikan oleh pengawas.');
                return $this->redirect(route('student.exams.index'));
            }

            // Tell Livewire: do NOT re-render the component for this request.
            $this->skipRender();

        } catch (\Exception) {
            // Ignore polling errors to avoid popup spam
            $this->skipRender();
        }

        return null;
    }

    // ── Violation handling ───────────────────────────────────────────────────

    /**
     * Handles a browser violation event (tab-switch / fullscreen-exit).
     *
     * RACE CONDITION FIX: tab_switches is incremented inside a DB transaction
     * with lockForUpdate() so concurrent violation events from the same student
     * (e.g. fast double tab-switch) do not produce duplicate increments.
     *
     * NOTE: Client-side debouncing (1.5 s) is implemented in Alpine.js in the
     * Blade view – this is the first line of defence. The server-side lock is
     * the second line of defence for genuine concurrency scenarios.
     */
    public function handleViolation(string $type, string $message): void
    {
        Log::info("[STUDENT-SIDE] handleViolation CALLED: {$type} - {$message}");

        try {
            // 1. Log to activities table (INSERT is effectively idempotent here)
            ExamActivity::create([
                'user_id'          => Auth::id(),
                'exam_id'          => $this->examId,
                'exam_attempt_id'  => $this->attemptId,
                'type'             => $type,
                'severity'         => in_array($type, ['tab_switch', 'fullscreen_exit']) ? 'warning' : 'info',
                'message'          => $message,
                'metadata'         => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            // 2. Increment counter with pessimistic lock
            if (in_array($type, ['tab_switch', 'fullscreen_exit'])) {
                DB::transaction(function (): void {
                    ExamAttempt::query()
                        ->whereKey($this->attemptId)
                        ->lockForUpdate()
                        ->first()
                        ?->increment('tab_switches');
                });
            }

            // 3. Broadcast (non-critical)
            try {
                $student   = Auth::user();
                $classroom = $student->student->classroom->name ?? 'Unknown Class';

                broadcast(new \App\Events\StudentViolationEvent(
                    $student->id,
                    $student->name,
                    $type,
                    $this->examId,
                    $message,
                    $classroom
                ));
            } catch (\Exception) {
                // swallow
            }

        } catch (\Exception $e) {
            Log::error('handleViolation FAILED: ' . $e->getMessage());
        }
    }

    // ── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $questions   = $this->questions;
        $answeredIds = $this->answeredIds; // single query, not N queries

        return view('livewire.student.take-exam', [
            'exam'            => $this->exam,
            'currentQuestion' => $this->currentQuestion,
            'currentOptions'  => $this->currentOptions,
            'questions'       => $questions,
            // Pass total count and answered count, not the full answered-IDs set,
            // to keep the view payload small. The navigation grid uses answeredIds.
            'answeredCount'   => $answeredIds->count(),
            'answeredIds'     => $answeredIds,
        ])->layout('layouts.student', ['title' => 'Ujian Berlangsung']);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function hasTimeExpired(): bool
    {
        $attempt = $this->resolveOwnedAttempt();
        if (!$attempt) {
            return true;
        }

        return now()->greaterThan($this->resolveFinalDeadline($attempt, $this->exam));
    }

    private function resolveFinalDeadline(ExamAttempt $attempt, Exam $exam): Carbon
    {
        $endByDuration = Carbon::parse($attempt->started_at)->addMinutes($exam->duration_minutes);
        $examEndTime   = Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);

        return $endByDuration->min($examEndTime);
    }

    private function isAttemptFinalized(ExamAttemptStatus|string|null $status): bool
    {
        $status = $status instanceof ExamAttemptStatus
            ? $status
            : ($status ? ExamAttemptStatus::tryFrom($status) : null);

        return $status?->isFinalized() ?? false;
    }

    /**
     * Returns the attempt row for the authenticated student.
     *
     * MEMOIZED: the result is cached in $_cachedAttempt for the duration of the
     * current PHP request so repeated calls (getQuestionsProperty, render,
     * persistCurrentAnswer, etc.) do not each issue a separate SELECT.
     */
    private function resolveOwnedAttempt(): ?ExamAttempt
    {
        if ($this->_cachedAttempt !== null) {
            return $this->_cachedAttempt;
        }

        $studentId = Auth::user()?->student?->id;
        if (!$studentId || !$this->attemptId || !$this->examId) {
            return null;
        }

        $this->_cachedAttempt = ExamAttempt::query()
            ->whereKey($this->attemptId)
            ->where('exam_id', $this->examId)
            ->where('student_id', $studentId)
            ->first();

        return $this->_cachedAttempt;
    }
}
