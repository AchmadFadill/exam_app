<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UndoShuffledLabelRemap extends Command
{
    protected $signature = 'exam:undo-shuffled-label-remap
        {--exam= : Filter by exam_id}
        {--attempt= : Filter by exam_attempt_id}
        {--chunk=500 : Chunk size}
        {--ignore-shuffle-flag : Process even when exams.shuffle_answers is currently false}
        {--include-active : Include in-progress/blocked attempts}
        {--apply : Persist updates (default dry-run)}';

    protected $description = 'Undo accidental label-index remap for shuffle-answers exams by mapping current selected option position back to canonical label (A-E).';

    public function handle(ScoringService $scoringService): int
    {
        $examId = $this->option('exam');
        $attemptId = $this->option('attempt');
        $apply = (bool) $this->option('apply');
        $ignoreShuffleFlag = (bool) $this->option('ignore-shuffle-flag');
        $includeActive = (bool) $this->option('include-active');
        $chunkSize = max(100, (int) $this->option('chunk'));

        if (!$examId && !$attemptId) {
            $this->error('Please provide at least one filter: --exam= or --attempt=');
            return self::FAILURE;
        }

        $base = DB::table('student_answers as sa')
            ->join('exam_attempts as ea', 'ea.id', '=', 'sa.exam_attempt_id')
            ->join('exams as e', 'e.id', '=', 'ea.exam_id')
            ->join('questions as q', 'q.id', '=', 'sa.question_id')
            ->where('q.type', 'multiple_choice')
            ->when(!$ignoreShuffleFlag, fn ($q) => $q->where('e.shuffle_answers', 1))
            ->whereNotNull('sa.selected_option_id')
            ->when($examId, fn ($q) => $q->where('ea.exam_id', (int) $examId))
            ->when($attemptId, fn ($q) => $q->where('ea.id', (int) $attemptId))
            ->when(!$includeActive, fn ($q) => $q->whereNotNull('ea.submitted_at'))
            ->orderBy('sa.id')
            ->select('sa.id');

        $inspected = 0;
        $eligible = 0;
        $reverted = 0;
        $skipped = 0;
        $affectedAttemptIds = [];

        $base->chunkById($chunkSize, function ($rows) use (&$inspected, &$eligible, &$reverted, &$skipped, &$affectedAttemptIds, $apply): bool {
            $ids = collect($rows)->pluck('id')->map(fn ($v) => (int) $v)->all();
            if (empty($ids)) {
                return true;
            }

            $answers = DB::table('student_answers as sa')
                ->join('exam_attempts as ea', 'ea.id', '=', 'sa.exam_attempt_id')
                ->select([
                    'sa.id',
                    'sa.exam_attempt_id',
                    'sa.question_id',
                    'sa.selected_option_id',
                    'ea.options_order',
                    'ea.exam_id',
                    'ea.student_id',
                ])
                ->whereIn('sa.id', $ids)
                ->orderBy('sa.id')
                ->get();

            $questionIds = $answers->pluck('question_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
            $optionsByQuestionAndLabel = QuestionOption::query()
                ->withTrashed()
                ->whereIn('question_id', $questionIds)
                ->get(['id', 'question_id', 'label'])
                ->groupBy(fn (QuestionOption $o) => (int) $o->question_id)
                ->map(fn ($rows) => $rows->keyBy(fn (QuestionOption $o) => strtoupper((string) $o->label)));

            foreach ($answers as $row) {
                $inspected++;

                $currentSelected = (int) ($row->selected_option_id ?? 0);
                if ($currentSelected <= 0) {
                    $skipped++;
                    continue;
                }

                $optionsOrder = json_decode((string) ($row->options_order ?? ''), true);
                $orderedOptionIds = is_array($optionsOrder)
                    ? ($optionsOrder[(string) $row->question_id] ?? null)
                    : null;

                if (!is_array($orderedOptionIds) || empty($orderedOptionIds)) {
                    $orderedOptionIds = $this->buildSeededOptionOrder(
                        questionId: (int) $row->question_id,
                        examId: (int) $row->exam_id,
                        studentId: (int) $row->student_id
                    );
                }

                if (!is_array($orderedOptionIds) || empty($orderedOptionIds)) {
                    $skipped++;
                    continue;
                }

                $pos = array_search($currentSelected, array_map('intval', $orderedOptionIds), true);
                if ($pos === false) {
                    $skipped++;
                    continue;
                }

                $label = chr(65 + (int) $pos); // 0->A
                $canonical = $optionsByQuestionAndLabel
                    ->get((int) $row->question_id)
                    ?->get($label);

                if (!$canonical) {
                    $skipped++;
                    continue;
                }

                $targetOptionId = (int) $canonical->id;
                $eligible++;

                if ($targetOptionId === $currentSelected) {
                    continue;
                }

                $reverted++;
                $affectedAttemptIds[(int) $row->exam_attempt_id] = true;

                if ($apply) {
                    StudentAnswer::query()
                        ->whereKey((int) $row->id)
                        ->update([
                            'selected_option_id' => $targetOptionId,
                            'answer' => (string) $targetOptionId,
                        ]);
                }
            }

            return true;
        }, 'sa.id', 'id');

        $recalculated = 0;
        if ($apply && !empty($affectedAttemptIds)) {
            $attemptIds = array_keys($affectedAttemptIds);

            foreach (array_chunk($attemptIds, 100) as $attemptChunk) {
                $attempts = ExamAttempt::query()
                    ->with('exam.questions.options')
                    ->whereIn('id', $attemptChunk)
                    ->get();

                foreach ($attempts as $attempt) {
                    if (!$attempt->exam) {
                        continue;
                    }

                    $summary = $scoringService->recalculateAttempt($attempt->exam, $attempt);
                    $attempt->update([
                        'total_score' => $summary['total_score'],
                        'percentage' => $summary['percentage'],
                        'passed' => $summary['passed'],
                    ]);
                    $recalculated++;
                }
            }
        }

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');
        $this->table(
            ['Metric', 'Value'],
            [
                ['inspected_answers', $inspected],
                ['eligible_answers', $eligible],
                ['reverted_answers', $reverted],
                ['skipped_answers', $skipped],
                ['affected_attempts', count($affectedAttemptIds)],
                ['recalculated_attempts', $recalculated],
            ]
        );

        if (!$apply) {
            $this->warn('No data changed. Re-run with --apply after validating the dry-run numbers.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int>
     */
    private function buildSeededOptionOrder(int $questionId, int $examId, int $studentId): array
    {
        $optionIds = QuestionOption::query()
            ->withTrashed()
            ->where('question_id', $questionId)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($optionIds)) {
            return [];
        }

        $seed = $studentId + $examId + $questionId;
        return $this->seededShuffle($optionIds, $seed);
    }

    /**
     * @param  array<int>  $items
     * @return array<int>
     */
    private function seededShuffle(array $items, int $seed): array
    {
        $items = array_values($items);
        $count = count($items);
        if ($count <= 1) {
            return $items;
        }

        $a = 1664525;
        $c = 1013904223;
        $m = 2 ** 32;
        $rand = $seed % $m;

        for ($i = $count - 1; $i > 0; $i--) {
            $rand = ($a * $rand + $c) % $m;
            $j = $rand % ($i + 1);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }

        return $items;
    }
}
