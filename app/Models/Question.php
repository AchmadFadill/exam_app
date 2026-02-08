<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'subject_id', 'title', 'type', 'text', 'image_path', 'explanation', 'answer_key', 'score'];

    protected static function booted()
    {
        static::deleting(function ($question) {
            if ($question->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($question->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
            }
        });
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) : null;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('label');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')
            ->withPivot('order', 'score');
    }

    public function correctOption()
    {
        return $this->options()->where('is_correct', true)->first();
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function isEssay(): bool
    {
        return $this->type === 'essay';
    }

    public static function distributeScoresByTitle($title)
    {
        $questions = self::where('title', $title)->get();
        $count = $questions->count();

        if ($count === 0) return;

        $totalScore = 100;
        $baseScore = floor($totalScore / $count);
        $remainder = $totalScore % $count;

        foreach ($questions as $index => $question) {
            $score = $baseScore;
            if ($index < $remainder) {
                $score++;
            }
            $question->update(['score' => $score]);
        }
    }
}
