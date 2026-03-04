<?php

namespace App\Console\Commands;

use App\Models\Exam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeScheduledExamQuestionOrder extends Command
{
    protected $signature = 'exam:normalize-order
        {--exam= : Normalize only one exam_id}
        {--status=scheduled : Filter exam status (default: scheduled)}
        {--apply : Persist changes (default dry-run)}';

    protected $description = 'Normalize question order for non-shuffled exams so fixed-order mode is deterministic.';

    public function handle(): int
    {
        $examId = $this->option('exam');
        $status = (string) $this->option('status');
        $apply = (bool) $this->option('apply');

        $query = Exam::query()
            ->select(['id', 'name', 'status'])
            ->where('shuffle_questions', false)
            ->when($examId, fn ($q) => $q->whereKey((int) $examId))
            ->when(!$examId && $status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('id');

        $totalExams = (int) $query->count();
        if ($totalExams === 0) {
            $this->warn('No matching exams found.');
            return self::SUCCESS;
        }

        $checked = 0;
        $changed = 0;
        $rowsUpdated = 0;

        $query->chunkById(100, function ($exams) use (&$checked, &$changed, &$rowsUpdated, $apply) {
            foreach ($exams as $exam) {
                $checked++;

                $rows = DB::table('exam_questions')
                    ->select(['id', 'order', 'question_id'])
                    ->where('exam_id', $exam->id)
                    ->orderBy('order')
                    ->orderBy('question_id')
                    ->get();

                if ($rows->isEmpty()) {
                    continue;
                }

                $updates = [];
                $newOrder = 1;
                foreach ($rows as $row) {
                    if ((int) $row->order !== $newOrder) {
                        $updates[] = [
                            'id' => (int) $row->id,
                            'order' => $newOrder,
                        ];
                    }
                    $newOrder++;
                }

                if (empty($updates)) {
                    continue;
                }

                $changed++;
                $rowsUpdated += count($updates);

                if ($apply) {
                    DB::transaction(function () use ($updates): void {
                        foreach ($updates as $update) {
                            DB::table('exam_questions')
                                ->where('id', $update['id'])
                                ->update(['order' => $update['order']]);
                        }
                    });
                }
            }
        });

        $this->line($apply ? 'Mode: APPLY' : 'Mode: DRY-RUN');
        $this->table(
            ['Metric', 'Value'],
            [
                ['checked_exams', $checked],
                ['changed_exams', $changed],
                ['updated_exam_question_rows', $rowsUpdated],
            ]
        );

        if (!$apply) {
            $this->warn('No data changed. Run again with --apply to persist.');
            $this->line('Example: php artisan exam:normalize-order --status=scheduled --apply');
        }

        return self::SUCCESS;
    }
}

