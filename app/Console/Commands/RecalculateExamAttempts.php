<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class RecalculateExamAttempts extends Command
{
    protected $signature = 'exam:recalculate-attempts
        {--attempt= : Recalculate only one exam_attempt_id}
        {--exam= : Recalculate attempts for one exam_id}
        {--student= : Recalculate attempts for one student_id}
        {--chunk=200 : Chunk size for batch processing}
        {--include-active : Include in_progress/blocked attempts}
        {--apply : Persist recalculated values (default is dry-run)}';

    protected $description = 'Recalculate total score, percentage, and pass flag for exam attempts using current scoring rules (safe mode skips active attempts).';

    public function handle(ScoringService $scoringService): int
    {
        $attemptId = $this->option('attempt');
        $examId = $this->option('exam');
        $studentId = $this->option('student');
        $apply = (bool) $this->option('apply');
        $includeActive = (bool) $this->option('include-active');
        $chunk = max(50, (int) $this->option('chunk'));

        $query = ExamAttempt::query()
            ->with(['exam.questions.options'])
            ->when($attemptId, fn ($q) => $q->where('id', (int) $attemptId))
            ->when($examId, fn ($q) => $q->where('exam_id', (int) $examId))
            ->when($studentId, fn ($q) => $q->where('student_id', (int) $studentId))
            ->when(!$includeActive, fn ($q) => $q->whereNotNull('submitted_at'));

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->warn('No exam attempts matched the provided filters.');
            return self::SUCCESS;
        }

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');
        $this->line("Total matched attempts: {$total}");

        $processed = 0;
        $changed = 0;
        $unchanged = 0;
        $errors = 0;

        $query->orderBy('id')->chunkById($chunk, function ($attempts) use (&$processed, &$changed, &$unchanged, &$errors, $scoringService, $apply) {
            foreach ($attempts as $attempt) {
                try {
                    if (!$attempt->exam) {
                        $errors++;
                        continue;
                    }

                    $summary = $scoringService->recalculateAttempt($attempt->exam, $attempt);

                    $newTotal = (int) $summary['total_score'];
                    $newPercentage = round((float) $summary['percentage'], 2);
                    $newPassed = (bool) $summary['passed'];

                    $oldTotal = (int) ($attempt->total_score ?? 0);
                    $oldPercentage = round((float) ($attempt->percentage ?? 0), 2);
                    $oldPassed = (bool) ($attempt->passed ?? false);

                    $isChanged = $oldTotal !== $newTotal
                        || $oldPercentage !== $newPercentage
                        || $oldPassed !== $newPassed;

                    if ($isChanged) {
                        $changed++;

                        if ($apply) {
                            $attempt->update([
                                'total_score' => $newTotal,
                                'percentage' => $newPercentage,
                                'passed' => $newPassed,
                            ]);
                        }
                    } else {
                        $unchanged++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $this->warn("Attempt {$attempt->id} failed: {$e->getMessage()}");
                } finally {
                    $processed++;
                }
            }

            $this->line("Processed {$processed} attempts...");
        });

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['matched_attempts', $total],
                ['processed', $processed],
                ['changed', $changed],
                ['unchanged', $unchanged],
                ['errors', $errors],
            ]
        );

        if (!$apply) {
            $this->warn('Dry-run only. Re-run with --apply to persist the recalculated values.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
