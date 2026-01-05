<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_session_id', 'question_id', 'selected_option_id',
        'essay_answer', 'score', 'is_flagged',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'score' => 'decimal:2',
    ];

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function isCorrect(): bool
    {
        return $this->selectedOption?->is_correct ?? false;
    }

    public function autoGrade(): void
    {
        if ($this->question->isMultipleChoice()) {
            $examQuestion = ExamQuestion::where('exam_id', $this->examSession->exam_id)
                ->where('question_id', $this->question_id)->first();
            $this->update(['score' => $this->isCorrect() ? ($examQuestion->score ?? 0) : 0]);
        }
    }
}
