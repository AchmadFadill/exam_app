<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use Illuminate\Console\Command;

class RegradeBySelectedOption extends Command
{
    protected $signature = 'exam:regrade-by-selected-option
        {--exam= : Regrade attempts for a specific exam_id}
        {--attempt= : Regrade a specific exam_attempt_id}
        {--student= : Regrade attempts for a specific student_id}
        {--chunk=200 : Chunk size}
        {--include-active : Include in_progress/blocked attempts}
        {--apply : Persist updates (default dry-run)}';

    protected $description = 'Strictly regrade MC answers using selected_option_id only (no legacy inference), then update attempt totals.';

    public function handle(): int
    {
        $examId = $this->option('exam');
        $attemptId = $this->option('attempt');
        $studentId = $this->option('student');
        $apply = (bool) $this->option('apply');
        $includeActive = (bool) $this->option('include-active');
        $chunk = max(50, (int) $this->option('chunk'));

        $query = ExamAttempt::query()
            ->with(['exam.questions.options', 'answers'])
            ->when($examId, fn ($q) => $q->where('exam_id', (int) $examId))
            ->when($attemptId, fn ($q) => $q->where('id', (int) $attemptId))
            ->when($studentId, fn ($q) => $q->where('student_id', (int) $studentId))
            ->when(!$includeActive, fn ($q) => $q->whereNotNull('submitted_at'))
            ->orderBy('id');

        $matched = (clone $query)->count();
        if ($matched === 0) {
            $this->warn('No attempts matched the filter.');
            return self::SUCCESS;
        }

        $processed = 0;
        $updatedAnswers = 0;
        $updatedAttempts = 0;

        $query->chunkById($chunk, function ($attempts) use (&$processed, &$updatedAnswers, &$updatedAttempts, $apply) {
            foreach ($attempts as $attempt) {
                $processed++;
                if (!$attempt->exam) {
                    continue;
                }

                $questionMeta = $attempt->exam->questions->mapWithKeys(function ($q) {
                    return [
                        (int) $q->id => [
                            'type' => (string) $q->type,
                            'score' => (int) ($q->pivot->score ?? 0),
                        ],
                    ];
                });

                $questionIds = $questionMeta->keys()->all();
                $optionById = QuestionOption::query()
                    ->withTrashed()
                    ->whereIn('question_id', $questionIds)
                    ->get(['id', 'question_id', 'is_correct'])
                    ->keyBy('id');

                $totalScore = 0;
                $answerChangedInAttempt = false;

                foreach ($attempt->answers as $answer) {
                    $meta = $questionMeta->get((int) $answer->question_id);
                    if (!$meta) {
                        continue;
                    }

                    if ($meta['type'] !== 'multiple_choice') {
                        $totalScore += (int) ($answer->score_awarded ?? 0);
                        continue;
                    }

                    $selectedId = (int) ($answer->selected_option_id ?? 0);
                    $option = $selectedId > 0 ? $optionById->get($selectedId) : null;

                    $validOptionForQuestion = $option && (int) $option->question_id === (int) $answer->question_id;
                    $isCorrect = $validOptionForQuestion ? (bool) $option->is_correct : false;
                    $scoreAwarded = $isCorrect ? (int) $meta['score'] : 0;

                    if ((bool) $answer->is_correct !== $isCorrect || (int) $answer->score_awarded !== $scoreAwarded) {
                        $updatedAnswers++;
                        $answerChangedInAttempt = true;
                        if ($apply) {
                            $answer->update([
                                'is_correct' => $isCorrect,
                                'score_awarded' => $scoreAwarded,
                            ]);
                        }
                    }

                    $totalScore += $scoreAwarded;
                }

                $maxScore = (int) $questionMeta->sum('score');
                $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 2) : 0.0;
                $passed = $percentage >= (float) $attempt->exam->passing_grade;

                if ((int) ($attempt->total_score ?? 0) !== $totalScore
                    || round((float) ($attempt->percentage ?? 0), 2) !== $percentage
                    || (bool) ($attempt->passed ?? false) !== $passed
                ) {
                    $updatedAttempts++;
                    if ($apply) {
                        $attempt->update([
                            'total_score' => $totalScore,
                            'percentage' => $percentage,
                            'passed' => $passed,
                        ]);
                    }
                } elseif ($answerChangedInAttempt && $apply) {
                    // Keep count semantics consistent if answers changed but aggregate happened to stay equal.
                    $updatedAttempts++;
                }
            }
        }, 'id');

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');
        $this->table(
            ['Metric', 'Value'],
            [
                ['matched_attempts', $matched],
                ['processed_attempts', $processed],
                ['updated_answer_rows', $updatedAnswers],
                ['updated_attempt_rows', $updatedAttempts],
            ]
        );

        if (!$apply) {
            $this->warn('Dry-run only. Re-run with --apply to persist changes.');
        }

        return self::SUCCESS;
    }
}

