<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecoverAnswersFromLegacySessions extends Command
{
    protected $signature = 'exam:recover-answers-from-legacy
        {--exam= : Filter by exam_id}
        {--attempt= : Filter by exam_attempt_id}
        {--chunk=100 : Chunk size}
        {--include-active : Include in-progress/blocked attempts}
        {--apply : Persist updates (default dry-run)}';

    protected $description = 'Recover student_answers (MC) from legacy exam_sessions/exam_answers when available, then recalculate attempts.';

    public function handle(ScoringService $scoringService): int
    {
        $examId = $this->option('exam');
        $attemptId = $this->option('attempt');
        $chunk = max(20, (int) $this->option('chunk'));
        $apply = (bool) $this->option('apply');
        $includeActive = (bool) $this->option('include-active');

        $query = ExamAttempt::query()
            ->with('exam.questions.options')
            ->when($examId, fn ($q) => $q->where('exam_id', (int) $examId))
            ->when($attemptId, fn ($q) => $q->where('id', (int) $attemptId))
            ->when(!$includeActive, fn ($q) => $q->whereNotNull('submitted_at'))
            ->orderBy('id');

        $matched = (clone $query)->count();
        if ($matched === 0) {
            $this->warn('No matching attempts.');
            return self::SUCCESS;
        }

        $processed = 0;
        $attemptsWithLegacy = 0;
        $updatedAnswers = 0;
        $insertedAnswers = 0;
        $recalculatedAttempts = 0;

        $query->chunkById($chunk, function ($attempts) use (
            &$processed,
            &$attemptsWithLegacy,
            &$updatedAnswers,
            &$insertedAnswers,
            &$recalculatedAttempts,
            $apply,
            $scoringService
        ) {
            foreach ($attempts as $attempt) {
                $processed++;

                $legacySession = DB::table('exam_sessions')
                    ->where('exam_id', (int) $attempt->exam_id)
                    ->where('student_id', (int) $attempt->student_id)
                    ->orderByDesc('finished_at')
                    ->orderByDesc('id')
                    ->first(['id']);

                if (!$legacySession) {
                    continue;
                }

                $legacyAnswers = DB::table('exam_answers')
                    ->join('questions as q', 'q.id', '=', 'exam_answers.question_id')
                    ->where('exam_answers.exam_session_id', (int) $legacySession->id)
                    ->where('q.type', 'multiple_choice')
                    ->whereNotNull('exam_answers.selected_option_id')
                    ->get([
                        'exam_answers.question_id',
                        'exam_answers.selected_option_id',
                    ])
                    ->keyBy('question_id');

                if ($legacyAnswers->isEmpty()) {
                    continue;
                }

                $attemptsWithLegacy++;

                $existing = StudentAnswer::query()
                    ->where('exam_attempt_id', (int) $attempt->id)
                    ->get()
                    ->keyBy('question_id');

                $changedInAttempt = false;

                foreach ($legacyAnswers as $questionId => $legacy) {
                    $questionId = (int) $questionId;
                    $legacyOptionId = (int) $legacy->selected_option_id;
                    if ($legacyOptionId <= 0) {
                        continue;
                    }

                    $current = $existing->get($questionId);
                    if ($current) {
                        if ((int) ($current->selected_option_id ?? 0) === $legacyOptionId) {
                            continue;
                        }

                        $updatedAnswers++;
                        $changedInAttempt = true;

                        if ($apply) {
                            $current->update([
                                'selected_option_id' => $legacyOptionId,
                                'answer' => (string) $legacyOptionId,
                            ]);
                        }
                    } else {
                        $insertedAnswers++;
                        $changedInAttempt = true;

                        if ($apply) {
                            StudentAnswer::create([
                                'exam_attempt_id' => (int) $attempt->id,
                                'question_id' => $questionId,
                                'selected_option_id' => $legacyOptionId,
                                'answer' => (string) $legacyOptionId,
                                'is_correct' => false,
                                'score_awarded' => 0,
                            ]);
                        }
                    }
                }

                if ($apply && $changedInAttempt && $attempt->exam) {
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
                ['matched_attempts', $matched],
                ['processed_attempts', $processed],
                ['attempts_with_legacy_session_answers', $attemptsWithLegacy],
                ['updated_student_answers', $updatedAnswers],
                ['inserted_student_answers', $insertedAnswers],
                ['recalculated_attempts', $recalculatedAttempts],
            ]
        );

        if (!$apply) {
            $this->warn('Dry-run only. Re-run with --apply to persist changes.');
        }

        return self::SUCCESS;
    }
}

