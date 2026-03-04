<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditQuestionDrift extends Command
{
    protected $signature = 'exam:audit-question-drift
        {--exam= : Filter by exam_id}
        {--limit=20 : Max sample rows}';

    protected $description = 'Audit whether questions/options were changed after students started/submitted attempts.';

    public function handle(): int
    {
        $examId = $this->option('exam');
        $limit = max(1, (int) $this->option('limit'));

        $base = DB::table('exam_attempts as ea')
            ->join('exams as e', 'e.id', '=', 'ea.exam_id')
            ->join('exam_questions as eq', 'eq.exam_id', '=', 'ea.exam_id')
            ->join('questions as q', 'q.id', '=', 'eq.question_id')
            ->whereNotNull('ea.submitted_at')
            ->when($examId, fn ($q) => $q->where('ea.exam_id', (int) $examId));

        $questionDriftCount = (clone $base)
            ->where('q.updated_at', '>', DB::raw('ea.started_at'))
            ->distinct('q.id', 'ea.id')
            ->count('ea.id');

        $optionDriftCount = (clone $base)
            ->join('question_options as qo', 'qo.question_id', '=', 'q.id')
            ->where('qo.updated_at', '>', DB::raw('ea.started_at'))
            ->distinct('qo.id', 'ea.id')
            ->count('ea.id');

        $samples = (clone $base)
            ->leftJoin('question_options as qo', 'qo.question_id', '=', 'q.id')
            ->where(function ($q) {
                $q->where('q.updated_at', '>', DB::raw('ea.started_at'))
                    ->orWhere('qo.updated_at', '>', DB::raw('ea.started_at'));
            })
            ->select([
                'ea.id as attempt_id',
                'ea.exam_id',
                'eq.question_id',
                'ea.started_at',
                'ea.submitted_at',
                'q.updated_at as question_updated_at',
                'qo.id as option_id',
                'qo.label as option_label',
                'qo.updated_at as option_updated_at',
            ])
            ->orderBy('ea.id')
            ->limit($limit)
            ->get();

        $this->table(
            ['Metric', 'Value'],
            [
                ['question_drift_attempt_rows', $questionDriftCount],
                ['option_drift_attempt_rows', $optionDriftCount],
                ['sample_rows', $samples->count()],
            ]
        );

        if ($samples->isNotEmpty()) {
            $this->newLine();
            $this->warn('Sample drift rows:');
            $this->table(
                ['attempt', 'exam', 'question', 'started_at', 'submitted_at', 'q_updated', 'opt_id', 'opt_label', 'opt_updated'],
                $samples->map(function ($row): array {
                    return [
                        (string) $row->attempt_id,
                        (string) $row->exam_id,
                        (string) $row->question_id,
                        (string) $row->started_at,
                        (string) $row->submitted_at,
                        (string) ($row->question_updated_at ?? '-'),
                        (string) ($row->option_id ?? '-'),
                        (string) ($row->option_label ?? '-'),
                        (string) ($row->option_updated_at ?? '-'),
                    ];
                })->all()
            );
        }

        return self::SUCCESS;
    }
}

