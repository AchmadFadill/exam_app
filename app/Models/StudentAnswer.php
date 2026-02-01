<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'answer',
        'selected_option_id',
        'is_correct',
        'score_awarded',
        'teacher_feedback',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // Relationships
    public function examAttempt()
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
