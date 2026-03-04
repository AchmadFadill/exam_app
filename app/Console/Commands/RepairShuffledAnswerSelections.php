<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Models\QuestionOption;
use App\Models\StudentAnswer;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairShuffledAnswerSelections extends Command
{
    protected $signature = 'exam:repair-shuffled-answers
        {--exam= : Filter by exam_id}
        {--attempt= : Filter by exam_attempt_id}
        {--chunk=500 : Chunk size}
        {--force-label-index : Also remap using selected option label index (A=0, B=1, ...) when raw answer is no longer positional}
        {--include-active : Include in-progress/blocked attempts}
        {--apply : Persist updates (default dry-run)}';

    protected $description = 'Repair multiple-choice answers for shuffle-answers exams by remapping positional legacy values using exam_attempts.options_order.';

    public function handle(ScoringService $scoringService): int
    {
        $examId = $this->option('exam');
        $attemptId = $this->option('attempt');
        $apply = (bool) $this->option('apply');
        $includeActive = (bool) $this->option('include-active');
        $forceLabelIndex = (bool) $this->option('force-label-index');
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
            ->where('e.shuffle_answers', 1)
            ->when($examId, fn ($q) => $q->where('ea.exam_id', (int) $examId))
            ->when($attemptId, fn ($q) => $q->where('ea.id', (int) $attemptId))
            ->when(!$includeActive, fn ($q) => $q->whereNotNull('ea.submitted_at'))
            ->orderBy('sa.id')
            ->select('sa.id');

        $inspected = 0;
        $eligible = 0;
        $remapped = 0;
        $skipped = 0;
        $affectedAttemptIds = [];

        $base->chunkById($chunkSize, function ($rows) use (&$inspected, &$eligible, &$remapped, &$skipped, &$affectedAttemptIds, $apply): bool {
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
                    'sa.answer',
                    'ea.options_order',
                    'ea.exam_id',
                    'ea.student_id',
                ])
                ->whereIn('sa.id', $ids)
                ->orderBy('sa.id')
                ->get();

            $selectedOptionIds = $answers
                ->pluck('selected_option_id')
                ->filter(fn ($id) => !is_null($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $selectedOptionMap = QuestionOption::query()
                ->withTrashed()
                ->whereIn('id', $selectedOptionIds)
                ->get(['id', 'label', 'question_id'])
                ->keyBy('id');

            $candidateOptionIds = [];
            $updates = [];

            foreach ($answers as $row) {
                $inspected++;

                $raw = trim((string) ($row->answer ?? ''));
                $index = $this->extractPositionIndex($raw);
                if ($index === null && $forceLabelIndex && !is_null($row->selected_option_id)) {
                    $selected = $selectedOptionMap->get((int) $row->selected_option_id);
                    if ($selected && (int) $selected->question_id === (int) $row->question_id) {
                        $index = $this->labelToIndex((string) ($selected->label ?? ''));
                    }
                }
                if ($index === null) {
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

                if (!is_array($orderedOptionIds) || !array_key_exists($index, $orderedOptionIds)) {
                    $skipped++;
                    continue;
                }

                $eligible++;
                $mappedOptionId = (int) $orderedOptionIds[$index];
                $candidateOptionIds[$mappedOptionId] = true;

                if ((int) ($row->selected_option_id ?? 0) === $mappedOptionId) {
                    continue;
                }

                $updates[] = [
                    'id' => (int) $row->id,
                    'exam_attempt_id' => (int) $row->exam_attempt_id,
                    'question_id' => (int) $row->question_id,
                    'selected_option_id' => $mappedOptionId,
                ];
            }

            if (empty($updates)) {
                return true;
            }

            $optionsById = QuestionOption::query()
                ->withTrashed()
                ->whereIn('id', array_keys($candidateOptionIds))
                ->get(['id', 'question_id'])
                ->keyBy('id');

            foreach ($updates as $upd) {
                $option = $optionsById->get((int) $upd['selected_option_id']);
                if (!$option) {
                    continue;
                }

                if ((int) $option->question_id !== (int) $upd['question_id']) {
                    continue;
                }

                $remapped++;
                $affectedAttemptIds[$upd['exam_attempt_id']] = true;

                if ($apply) {
                    StudentAnswer::query()
                        ->whereKey((int) $upd['id'])
                        ->update([
                            'selected_option_id' => (int) $upd['selected_option_id'],
                            'answer' => (string) $upd['selected_option_id'],
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
                ['eligible_positional_answers', $eligible],
                ['remapped_answers', $remapped],
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

    private function extractPositionIndex(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^[A-Z]$/i', $raw) === 1) {
            $index = ord(strtoupper($raw)) - 65;
            return $index >= 0 ? $index : null;
        }

        if (!is_numeric($raw)) {
            return null;
        }

        $n = (int) $raw;
        if ($n < 0 || $n > 10) {
            return null;
        }

        return $n > 0 ? $n - 1 : 0;
    }

    private function labelToIndex(string $label): ?int
    {
        $label = trim(strtoupper($label));
        if (preg_match('/^[A-Z]$/', $label) !== 1) {
            return null;
        }

        $idx = ord($label) - 65;
        return $idx >= 0 ? $idx : null;
    }

    /**
     * Build deterministic option order when attempt snapshot is missing.
     *
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
