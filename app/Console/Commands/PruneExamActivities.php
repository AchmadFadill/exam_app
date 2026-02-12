<?php

namespace App\Console\Commands;

use App\Models\ExamActivity;
use Illuminate\Console\Command;

class PruneExamActivities extends Command
{
    protected $signature = 'exam:prune-activity-logs {--hours=24 : Keep logs newer than this amount of hours}';

    protected $description = 'Delete exam activity logs older than a configured number of hours (default: 24).';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $threshold = now()->subHours($hours);

        $deleted = ExamActivity::query()
            ->where('created_at', '<', $threshold)
            ->delete();

        $this->info("Pruned {$deleted} activity logs older than {$hours} hour(s).");

        return self::SUCCESS;
    }
}

