<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixExamAnswers extends Command
{
    protected $signature = 'exam:fix-answers
        {--attempt= : Fix only one exam_attempt_id}
        {--limit=0 : Max rows to inspect (0 = unlimited)}
        {--apply : Apply changes (default is dry-run)}';

    protected $description = 'Auto-fix inconsistent multiple-choice answer references and re-calculate affected attempts.';

    public function handle(ScoringService $scoringService): int
    {
        $attemptId = $this->option('attempt');
        $limit = max(0, (int) $this->option('limit'));
        $apply = (bool) $this->option('apply');

        $base = StudentAnswer::query()
            ->join('questions as q', 'q.id', '=', 'student_answers.question_id')
            ->where('q.type', 'multiple_choice')
            ->select('student_answers.id')
            ->when($attemptId, fn ($q) => $q->where('student_answers.exam_attempt_id', (int) $attemptId));

        if ($limit > 0) {
            $base->limit($limit);
        }

        $answerIds = $base->pluck('student_answers.id')->all();
        if (empty($answerIds)) {
            $this->info('No candidate answers found.');
            return self::SUCCESS;
        }

        $answers = StudentAnswer::query()
            ->with(['question.options'])
            ->whereIn('id', $answerIds)
            ->get();

        $fixed = 0;
        $skipped = 0;
        $affectedAttemptIds = [];

        foreach ($answers as $answer) {
            $replacement = $this->findReplacementOptionId($answer);
            if (!$replacement || $replacement === $answer->selected_option_id) {
                $skipped++;
                continue;
            }

            $affectedAttemptIds[$answer->exam_attempt_id] = true;
            $fixed++;

            if ($apply) {
                $answer->update([
                    'selected_option_id' => $replacement,
                    // Keep compatibility with existing flow that persists option id string in `answer`.
                    'answer' => (string) $replacement,
                ]);
            }
        }

        $recalculated = 0;
        if ($apply && !empty($affectedAttemptIds)) {
            DB::transaction(function () use (&$recalculated, $affectedAttemptIds, $scoringService): void {
                $attempts = ExamAttempt::query()
                    ->with('exam.questions')
                    ->whereIn('id', array_keys($affectedAttemptIds))
                    ->get();

                foreach ($attempts as $attempt) {
                    $summary = $scoringService->recalculateAttempt($attempt->exam, $attempt);
                    $attempt->update([
                        'total_score' => $summary['total_score'],
                        'percentage' => $summary['percentage'],
                        'passed' => $summary['passed'],
                    ]);
                    $recalculated++;
                }
            });
        }

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');
        $this->table(
            ['Metric', 'Value'],
            [
                ['inspected_answers', count($answerIds)],
                ['fixed_selected_option_links', $fixed],
                ['skipped_or_no_mapping', $skipped],
                ['affected_attempts', count($affectedAttemptIds)],
                ['recalculated_attempts', $recalculated],
            ]
        );

        if (!$apply) {
            $this->warn('No data changed. Re-run with --apply to persist fixes.');
        }

        return self::SUCCESS;
    }

    private function findReplacementOptionId(StudentAnswer $answer): ?int
    {
        $questionId = (int) $answer->question_id;

        $raw = trim((string) ($answer->answer ?? ''));
        if ($raw === '') {
            return null;
        }

        // 2) Numeric answer value can represent option id in this app.
        if (is_numeric($raw)) {
            $numericId = (int) $raw;
            $direct = QuestionOption::query()
                ->where('id', $numericId)
                ->where('question_id', $questionId)
                ->first();

            if ($direct) {
                return (int) $direct->id;
            }
        }
        return null;
    }
}
