<?php

namespace App\Enums;

enum ExamAttemptStatus: string
{
    case InProgress = 'in_progress';
    case Blocked = 'blocked';
    case Submitted = 'submitted';
    case Graded = 'graded';
    case Abandoned = 'abandoned';
    case Completed = 'completed';
    case Ongoing = 'ongoing';
    case TimedOut = 'timed_out';

    public function isFinalized(): bool
    {
        return in_array($this, self::finalized(), true);
    }

    /**
     * @return array<self>
     */
    public static function finalized(): array
    {
        return [
            self::Submitted,
            self::Graded,
            self::Completed,
            self::Abandoned,
            self::TimedOut,
        ];
    }
}
