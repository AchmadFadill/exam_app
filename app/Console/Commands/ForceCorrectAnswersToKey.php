<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class ForceCorrectAnswersToKey extends Command
{
    protected $signature = 'exam:force-correct-to-key
        {--exam= : Target exam_id (required)}
        {--student= : Optional student_id filter}
        {--attempt= : Optional exam_attempt_id filter}
        {--chunk=200 : Chunk size}
        {--include-active : Include in_progress/blocked attempts}
        {--apply : Persist changes (default dry-run)}';

    protected $description = 'Force all wrong multiple-choice answers to the current correct key for selected exam attempts, then recalculate totals.';

    public function handle(ScoringService $scoringService): int
    {
        $examId = $this->option('exam');
        $studentId = $this->option('student');
        $attemptId = $this->option('attempt');
        $chunk = max(50, (int) $this->option('chunk'));
        $apply = (bool) $this->option('apply');
        $includeActive = (bool) $this->option('include-active');

        if (!$examId) {
            $this->error('--exam is required.');
            return self::FAILURE;
        }

        $attemptsQuery = ExamAttempt::query()
            ->with('exam.questions.options')
            ->where('exam_id', (int) $examId)
            ->when($studentId, fn ($q) => $q->where('student_id', (int) $studentId))
            ->when($attemptId, fn ($q) => $q->where('id', (int) $attemptId))
            ->when(!$includeActive, fn ($q) => $q->whereNotNull('submitted_at'))
            ->orderBy('id');

        $matchedAttempts = (clone $attemptsQuery)->count();
        if ($matchedAttempts === 0) {
            $this->warn('No attempts matched filters.');
            return self::SUCCESS;
        }

        $processedAttempts = 0;
        $updatedAnswerRows = 0;
        $recalculatedAttempts = 0;

        $attemptsQuery->chunkById($chunk, function ($attempts) use (
            &$processedAttempts,
            &$updatedAnswerRows,
            &$recalculatedAttempts,
            $apply,
            $scoringService
        ) {
            foreach ($attempts as $attempt) {
                $processedAttempts++;
                if (!$attempt->exam) {
                    continue;
                }

                $mcQuestionIds = $attempt->exam->questions
                    ->where('type', 'multiple_choice')
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if (empty($mcQuestionIds)) {
                    continue;
                }

                $correctOptionByQuestion = QuestionOption::query()
                    ->whereIn('question_id', $mcQuestionIds)
                    ->where('is_correct', true)
                    ->get(['id', 'question_id'])
                    ->keyBy('question_id');

                if ($correctOptionByQuestion->isEmpty()) {
                    continue;
                }

                $answers = StudentAnswer::query()
                    ->where('exam_attempt_id', (int) $attempt->id)
                    ->whereIn('question_id', $mcQuestionIds)
                    ->get();

                $changed = false;
                foreach ($answers as $answer) {
                    // Skip unanswered rows for PG.
                    if (is_null($answer->selected_option_id) && trim((string) ($answer->answer ?? '')) === '') {
                        continue;
                    }

                    $correctOption = $correctOptionByQuestion->get((int) $answer->question_id);
                    if (!$correctOption) {
                        continue;
                    }

                    $currentOptionId = (int) ($answer->selected_option_id ?? 0);
                    $targetOptionId = (int) $correctOption->id;

                    if ($currentOptionId === $targetOptionId && (bool) $answer->is_correct === true) {
                        continue;
                    }

                    $updatedAnswerRows++;
                    $changed = true;

                    if ($apply) {
                        $answer->update([
                            'selected_option_id' => $targetOptionId,
                            'answer' => (string) $targetOptionId,
                        ]);
                    }
                }

                if ($apply && $changed) {
                    $summary = $scoringService->recalculateAttempt($attempt->exam, $attempt);
                    $attempt->update([
                        'total_score' => $summary['total_score'],
                        'percentage' => $summary['percentage'],
                        'passed' => $summary['passed'],
                    ]);
                    $recalculatedAttempts++;
                }
            }
        }, 'id');

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');
        $this->table(
            ['Metric', 'Value'],
            [
                ['matched_attempts', $matchedAttempts],
                ['processed_attempts', $processedAttempts],
                ['updated_answer_rows', $updatedAnswerRows],
                ['recalculated_attempts', $recalculatedAttempts],
            ]
        );

        if (!$apply) {
            $this->warn('Dry-run only. Re-run with --apply to persist updates.');
        }

        return self::SUCCESS;
    }
}

