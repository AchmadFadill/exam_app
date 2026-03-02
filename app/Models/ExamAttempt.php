<?php

namespace App\Models;

use App\Enums\ExamAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'last_seen_at',
        'submitted_at',
        'status',
        'tab_switches',
        // ── Snapshot randomization ─────────────────────────────────────────────
        // Stored ONCE when the student clicks "Start".
        // Never regenerated mid-exam to prevent randomization drift.
        'question_order',   // JSON: [question_id, …]          – ordered list
        'options_order',    // JSON: { "question_id": [option_id, …] }
        // ──────────────────────────────────────────────────────────────────────
        'total_score',
        'percentage',
        'passed',
        'teacher_notes',
    ];

    protected $casts = [
        'started_at'     => 'datetime',
        'last_seen_at'   => 'datetime',
        'submitted_at'   => 'datetime',
        'status'         => ExamAttemptStatus::class,
        'passed'         => 'boolean',
        'percentage'     => 'decimal:2',
        'question_order' => 'array',   // auto-decoded from / encoded to JSON
        'options_order'  => 'array',   // auto-decoded from / encoded to JSON
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
