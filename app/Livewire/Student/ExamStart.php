<?php

namespace App\Livewire\Student;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * ExamStart – handles the pre-exam token check and attempt creation.
 *
 * KEY FIX (Randomization Drift):
 *   question_order and options_order are generated ONCE here and persisted
 *   to the exam_attempts row.  TakeExam and ExamController never re-shuffle;
 *   they always read from this snapshot.
 */
class ExamStart extends Component
{
    use AuthorizesRequests;

    public $examId;
    public $token = '';

    public function mount($id): void
    {
        $this->examId = $id;

        $exam = $this->exam;
        if (!$exam) {
            abort(404);
        }

        // If an active attempt already exists, resume it immediately.
        $attempt = ExamAttempt::where('exam_id', $id)
            ->where('student_id', Auth::user()->student->id)
            ->first();

        if ($attempt) {
            if ($attempt->status === ExamAttemptStatus::InProgress) {
                $this->redirect(route('student.exam.show', $id));
            }
            // Submitted / graded – leave on this page (or redirect to results).
        }
    }

    public function getExamProperty(): Exam
    {
        return Exam::with(['subject', 'teacher.user', 'examQuestions'])
            ->findOrFail($this->examId);
    }

    public function startExam(): void
    {
        $student = Auth::user()->student;

        // Validate: time window
        if (!$this->canStart()) {
            $this->dispatch('notify', [
                'message' => 'Ujian belum dimulai atau sudah berakhir.',
                'type'    => 'error',
            ]);
            return;
        }

        // Validate token if the exam requires one
        if ($this->exam->token && trim(strtoupper($this->token)) !== trim(strtoupper($this->exam->token))) {
            $this->dispatch('notify', [
                'message' => 'Token ujian tidak valid. Pastikan token yang dimasukkan benar.',
                'type'    => 'error',
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($student) {
                // Prevent duplicate concurrent starts (race condition guard)
                $existing = ExamAttempt::where('exam_id', $this->examId)
                    ->where('student_id', $student->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    // Already created – just redirect
                    return;
                }

                // ── Build the SNAPSHOT ────────────────────────────────────────
                $exam = $this->exam;

                // Fixed-order mode: enforce stable ASC by question_id.
                // This prevents legacy reversed pivot order from showing descending questions.
                $orderedQuestionIds = $exam->examQuestions
                    ->sortBy(fn ($row) => (int) $row->question_id)
                    ->pluck('question_id')
                    ->values()
                    ->toArray();

                $questionOrder = $orderedQuestionIds;

                if ($exam->shuffle_questions) {
                    // Use PHP shuffle with a fixed seed derived from student+exam
                    $questionOrder = $this->seededShuffle($orderedQuestionIds, $student->id + $exam->id);
                }

                // Load options for every question in a single query
                $allQuestions = \App\Models\Question::with('options')
                    ->whereIn('id', $orderedQuestionIds)
                    ->get()
                    ->keyBy('id');

                $optionsOrder = [];
                foreach ($questionOrder as $qId) {
                    $question = $allQuestions->get($qId);
                    if (!$question) {
                        continue;
                    }

                    $optionIds = $question->options->pluck('id')->toArray();

                    // Answer shuffling is globally disabled for stability.
                    // Keep canonical option order for all attempts.

                    $optionsOrder[(string) $qId] = $optionIds;
                }
                // ─────────────────────────────────────────────────────────────

                ExamAttempt::create([
                    'exam_id'        => $this->examId,
                    'student_id'     => $student->id,
                    'started_at'     => now(),
                    'status'         => ExamAttemptStatus::InProgress,
                    'question_order' => $questionOrder,
                    'options_order'  => $optionsOrder,
                ]);
            });

            $this->redirect(route('student.exam.show', $this->examId));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ExamStart::startExam failed: ' . $e->getMessage());
            $this->dispatch('notify', [
                'message' => 'Gagal memulai ujian: ' . $e->getMessage(),
                'type'    => 'error',
            ]);
        }
    }

    public function canStart(): bool
    {
        $exam = $this->exam;

        return $exam->status === 'scheduled'
            && $exam->date->isToday()
            && now()->between(
                \Carbon\Carbon::parse($exam->start_time),
                \Carbon\Carbon::parse($exam->end_time)
            );
    }

    /**
     * Deterministic Fisher-Yates shuffle using a seeded LCG PRNG.
     * PHP's built-in shuffle() is NOT seedable, so we implement our own.
     *
     * @template T
     * @param  T[]  $items
     * @param  int  $seed
     * @return T[]
     */
    private function seededShuffle(array $items, int $seed): array
    {
        $items = array_values($items);
        $count = count($items);
        if ($count <= 1) {
            return $items;
        }

        // LCG parameters (same as glibc / Java)
        $a    = 1664525;
        $c    = 1013904223;
        $m    = 2 ** 32;
        $rand = $seed % $m;

        for ($i = $count - 1; $i > 0; $i--) {
            $rand = ($a * $rand + $c) % $m;
            $j    = $rand % ($i + 1);

            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }

    public function render()
    {
        return view('livewire.student.exam-start', [
            'exam' => $this->exam,
        ])->layout('layouts.student', ['title' => 'Persiapan Ujian']);
    }
}
