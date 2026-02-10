<?php

namespace App\Actions\Exam;

use App\Enums\ExamAttemptStatus;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Support\Facades\DB;

class ProcessExamSubmissionAction
{
    public function __construct(private readonly ScoringService $scoringService)
    {
    }

    /**
     * @param  array<int|string, mixed>  $answers
     * @param  callable(ExamAttempt):void|null  $beforeRecalculate
     */
    public function execute(
        Exam $exam,
        ExamAttempt $attempt,
        array $answers = [],
        ?callable $beforeRecalculate = null
    ): ExamAttempt {
        return DB::transaction(function () use ($exam, $attempt, $answers, $beforeRecalculate): ExamAttempt {
            $lockedAttempt = ExamAttempt::query()
                ->whereKey($attempt->id)
                ->where('exam_id', $exam->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isFinalized($lockedAttempt->status)) {
                return $lockedAttempt;
            }

            if (!empty($answers)) {
                $this->upsertAnswers($exam, $lockedAttempt, $answers);
            }

            if ($beforeRecalculate) {
                $beforeRecalculate($lockedAttempt);
            }

            $summary = $this->scoringService->recalculateAttempt($exam, $lockedAttempt);

            $lockedAttempt->update([
                'submitted_at' => now(),
                'status' => $summary['has_essay'] ? ExamAttemptStatus::Submitted : ExamAttemptStatus::Graded,
                'total_score' => $summary['total_score'],
                'percentage' => $summary['percentage'],
                'passed' => $summary['passed'],
            ]);

            return $lockedAttempt->refresh();
        });
    }

    /**
     * @param  array<int|string, mixed>  $answers
     */
    private function upsertAnswers(Exam $exam, ExamAttempt $attempt, array $answers): void
    {
        $examQuestions = $exam->questions->keyBy('id');

        foreach ($answers as $questionId => $answerValue) {
            $question = $examQuestions[(int) $questionId] ?? null;
            if (!$question) {
                continue;
            }

            $scored = $this->scoringService->scoreSingleAnswer($exam, $question, $answerValue);

            StudentAnswer::updateOrCreate(
                [
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => (int) $questionId,
                ],
                $scored
            );
        }
    }

    private function isFinalized(ExamAttemptStatus|string|null $status): bool
    {
        $status = $status instanceof ExamAttemptStatus
            ? $status
            : ($status ? ExamAttemptStatus::tryFrom((string) $status) : null);

        return $status?->isFinalized() ?? false;
    }
}
