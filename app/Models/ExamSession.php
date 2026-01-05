<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id', 'student_id', 'started_at', 'finished_at',
        'violations_count', 'is_submitted', 'total_score',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'is_submitted' => 'boolean',
        'total_score' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function getRemainingTimeAttribute(): int
    {
        if (!$this->started_at) {
            return $this->exam->duration_minutes * 60;
        }
        $elapsed = now()->diffInSeconds($this->started_at);
        return max(0, ($this->exam->duration_minutes * 60) - $elapsed);
    }

    public function hasExpired(): bool
    {
        return $this->remaining_time <= 0;
    }

    public function recordViolation(): void
    {
        $this->increment('violations_count');
    }

    public function calculateScore(): void
    {
        $this->update(['total_score' => $this->answers()->sum('score')]);
    }
}
