<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class InvertExamAnswersAgainstKey extends Command
{
    protected $signature = 'exam:invert-answers-against-key
        {--exam= : Target exam_id (required)}
        {--student= : Optional student_id filter}
        {--attempt= : Optional exam_attempt_id filter}
        {--chunk=200 : Chunk size}
        {--include-active : Include in_progress/blocked attempts}
        {--apply : Persist changes (default dry-run)}';

    protected $description = 'Invert MC answers against key for selected exams: currently-correct answers become wrong, wrong answers become correct.';

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
        $skippedRows = 0;
        $recalculatedAttempts = 0;

        $attemptsQuery->chunkById($chunk, function ($attempts) use (
            &$processedAttempts,
            &$updatedAnswerRows,
            &$skippedRows,
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

                $optionsByQuestion = QuestionOption::query()
                    ->whereIn('question_id', $mcQuestionIds)
                    ->get(['id', 'question_id', 'is_correct'])
                    ->groupBy('question_id');

                $answers = StudentAnswer::query()
                    ->where('exam_attempt_id', (int) $attempt->id)
                    ->whereIn('question_id', $mcQuestionIds)
                    ->get();

                $changed = false;

                foreach ($answers as $answer) {
                    $questionOptions = $optionsByQuestion->get((int) $answer->question_id);
                    if (!$questionOptions || $questionOptions->isEmpty()) {
                        $skippedRows++;
                        continue;
                    }

                    $correct = $questionOptions->firstWhere('is_correct', true);
                    if (!$correct) {
                        $skippedRows++;
                        continue;
                    }

                    $currentOptionId = (int) ($answer->selected_option_id ?? 0);
                    if ($currentOptionId <= 0) {
                        $skippedRows++;
                        continue;
                    }

                    $targetOptionId = null;

                    if ($currentOptionId === (int) $correct->id) {
                        // Currently correct => switch to a deterministic wrong option.
                        $wrong = $questionOptions
                            ->where('is_correct', false)
                            ->sortBy('id')
                            ->first();

                        if (!$wrong) {
                            $skippedRows++;
                            continue;
                        }

                        $targetOptionId = (int) $wrong->id;
                    } else {
                        // Currently wrong => switch to correct key.
                        $targetOptionId = (int) $correct->id;
                    }

                    if ($targetOptionId === $currentOptionId) {
                        $skippedRows++;
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
                ['skipped_rows', $skippedRows],
                ['recalculated_attempts', $recalculatedAttempts],
            ]
        );

        if (!$apply) {
            $this->warn('Dry-run only. Re-run with --apply to persist changes.');
        }

        return self::SUCCESS;
    }
}

