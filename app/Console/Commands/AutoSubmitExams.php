<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExamAttempt;
use Carbon\Carbon;

class AutoSubmitExams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:auto-submit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically submit exams that have exceeded their time limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-submission check...');

        $attempts = ExamAttempt::with('exam.questions')
            ->where('status', 'in_progress')
            ->get();

        $count = 0;

        foreach ($attempts as $attempt) {
            $exam = $attempt->exam;
            $startedAt = Carbon::parse($attempt->started_at);
            
            // Calculate limits
            $durationLimit = $startedAt->copy()->addMinutes($exam->duration_minutes);
            $hardLimit = Carbon::parse($exam->date->format('Y-m-d') . ' ' . $exam->end_time);
            
            // Allow 2 minutes buffer for latency/cron delay
            $buffer = 2; // minutes
            
            $shouldSubmit = false;
            $reason = '';

            if (now()->gt($durationLimit->addMinutes($buffer))) {
                $shouldSubmit = true;
                $reason = 'Duration Exceeded';
            } elseif (now()->gt($hardLimit->addMinutes($buffer))) {
                $shouldSubmit = true;
                $reason = 'Exam End Time Exceeded';
            }

            if ($shouldSubmit) {
                $this->submitAttempt($attempt, $reason);
                $count++;
            }
        }

        $this->info("Processed {$count} expired attempts.");
    }

    protected function submitAttempt($attempt, $reason)
    {
        // Calculate scores similar to TakeExam logic
        $totalScore = $attempt->answers->sum('score_awarded');
        $maxScore = $attempt->exam->questions->sum('pivot.score');
        $hasEssay = $attempt->exam->questions->where('type', 'essay')->count() > 0;

        $attempt->update([
            'submitted_at' => now(),
            'status' => $hasEssay ? 'submitted' : 'graded',
            'total_score' => $totalScore,
            'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0,
            'teacher_notes' => "Auto-submitted by system: {$reason}"
        ]);

        $this->line("Submitted attempt ID {$attempt->id} for Student ID {$attempt->student_id} ({$reason})");
    }
}
