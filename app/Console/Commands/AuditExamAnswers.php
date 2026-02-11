<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuditExamAnswers extends Command
{
    protected $signature = 'exam:audit-answers
        {--limit=100 : Maximum rows shown per anomaly type}
        {--attempt= : Filter by a specific exam_attempt_id}
        {--export= : Export anomalies to CSV under storage/app/<path>}';

    protected $description = 'Audit student answers for scoring/data-integrity anomalies.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $attemptId = $this->option('attempt');

        $definitions = [
            'numeric_answer_without_selected_option' => fn (Builder $q) => $q
                ->where('q.type', 'multiple_choice')
                ->whereNull('sa.selected_option_id')
                ->whereRaw("TRIM(COALESCE(sa.answer, '')) REGEXP '^[0-9]+$'"),

            'selected_option_missing' => fn (Builder $q) => $q
                ->where('q.type', 'multiple_choice')
                ->whereNotNull('sa.selected_option_id')
                ->whereNull('qo.id'),

            'selected_option_question_mismatch' => fn (Builder $q) => $q
                ->where('q.type', 'multiple_choice')
                ->whereNotNull('sa.selected_option_id')
                ->whereNotNull('qo.id')
                ->whereColumn('qo.question_id', '!=', 'sa.question_id'),

            'is_correct_true_but_option_not_correct' => fn (Builder $q) => $q
                ->where('q.type', 'multiple_choice')
                ->where('sa.is_correct', true)
                ->where(function (Builder $sub): void {
                    $sub->whereNull('qo.id')->orWhere('qo.is_correct', false);
                }),

            'is_correct_false_but_option_correct' => fn (Builder $q) => $q
                ->where('q.type', 'multiple_choice')
                ->where('sa.is_correct', false)
                ->whereNotNull('qo.id')
                ->where('qo.is_correct', true),
        ];

        $counts = [];
        $allRows = collect();

        foreach ($definitions as $name => $constraint) {
            $countQuery = $this->baseQuery($attemptId);
            $constraint($countQuery);
            $counts[$name] = (clone $countQuery)->count();

            if ($counts[$name] === 0) {
                continue;
            }

            $rowsQuery = $this->baseQuery($attemptId)->selectRaw('? as anomaly_type', [$name]);
            $constraint($rowsQuery);

            $rows = $rowsQuery
                ->orderBy('sa.id')
                ->limit($limit)
                ->get();

            $allRows = $allRows->concat($rows);
        }

        $total = array_sum($counts);

        $this->info('Audit ringkasan:');
        $this->table(
            ['Anomali', 'Jumlah'],
            collect($counts)->map(fn ($v, $k) => [$k, $v])->values()->all()
        );
        $this->line("Total anomali: {$total}");

        if ($allRows->isNotEmpty()) {
            $this->newLine();
            $this->warn('Contoh data anomali:');
            $this->table(
                ['type', 'answer_id', 'attempt_id', 'exam', 'student', 'question_id', 'selected_option_id', 'answer', 'is_correct', 'score_awarded'],
                $allRows->map(function ($row): array {
                    return [
                        $row->anomaly_type,
                        $row->answer_id,
                        $row->attempt_id,
                        $row->exam_name,
                        $row->student_name,
                        $row->question_id,
                        $row->selected_option_id,
                        $row->answer,
                        $row->is_correct,
                        $row->score_awarded,
                    ];
                })->all()
            );
        }

        $exportPath = $this->option('export');
        if ($exportPath) {
            $this->exportCsv($exportPath, $allRows);
            $this->info('CSV export selesai: storage/app/' . ltrim($exportPath, '/'));
        }

        return self::SUCCESS;
    }

    private function baseQuery(?string $attemptId): Builder
    {
        $q = DB::table('student_answers as sa')
            ->join('questions as q', 'q.id', '=', 'sa.question_id')
            ->join('exam_attempts as ea', 'ea.id', '=', 'sa.exam_attempt_id')
            ->join('exams as e', 'e.id', '=', 'ea.exam_id')
            ->join('students as s', 's.id', '=', 'ea.student_id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('question_options as qo', 'qo.id', '=', 'sa.selected_option_id')
            ->select([
                'sa.id as answer_id',
                'sa.exam_attempt_id as attempt_id',
                'sa.question_id',
                'sa.selected_option_id',
                'sa.answer',
                'sa.is_correct',
                'sa.score_awarded',
                'e.name as exam_name',
                'u.name as student_name',
            ]);

        if ($attemptId !== null && $attemptId !== '') {
            $q->where('sa.exam_attempt_id', (int) $attemptId);
        }

        return $q;
    }

    private function exportCsv(string $path, Collection $rows): void
    {
        $fullPath = storage_path('app/' . ltrim($path, '/'));
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $fp = fopen($fullPath, 'w');
        if ($fp === false) {
            throw new \RuntimeException('Cannot open file for export: ' . $fullPath);
        }

        fputcsv($fp, [
            'anomaly_type',
            'answer_id',
            'attempt_id',
            'exam_name',
            'student_name',
            'question_id',
            'selected_option_id',
            'answer',
            'is_correct',
            'score_awarded',
        ]);

        foreach ($rows as $row) {
            fputcsv($fp, [
                $row->anomaly_type ?? '',
                $row->answer_id ?? '',
                $row->attempt_id ?? '',
                $row->exam_name ?? '',
                $row->student_name ?? '',
                $row->question_id ?? '',
                $row->selected_option_id ?? '',
                $row->answer ?? '',
                $row->is_correct ?? '',
                $row->score_awarded ?? '',
            ]);
        }

        fclose($fp);
    }
}

