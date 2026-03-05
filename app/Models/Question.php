<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['teacher_id', 'subject_id', 'question_group_id', 'title', 'type', 'text', 'image_path', 'explanation', 'answer_key', 'score'];

    protected $casts = [
        'score' => 'float',
    ];

    protected static function booted()
    {
        static::deleting(function ($question) {
            if ($question->isForceDeleting() && $question->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($question->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
            }

            if ($question->isForceDeleting()) {
                $question->options()->withTrashed()->get()->each->forceDelete();
                return;
            }

            $question->options()->get()->each->delete();
        });

        static::restoring(function ($question) {
            $question->options()->withTrashed()->restore();
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

    public function questionGroup(): BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class);
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

    public function hasAttemptedExamUsage(): bool
    {
        return $this->exams()
            ->whereHas('attempts', function ($query) {
                $query->whereNotNull('submitted_at');
            })
            ->exists();
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

    public static function distributeScoresByGroupId(int $groupId): void
    {
        $questions = self::where('question_group_id', $groupId)
            ->orderBy('id')
            ->get();

        $count = $questions->count();
        if ($count === 0) {
            return;
        }

        $totalScore = 100;
        $baseScore = intdiv($totalScore, $count);
        $remainder = $totalScore % $count;

        foreach ($questions as $index => $question) {
            $score = $baseScore + ($index < $remainder ? 1 : 0);
            $question->update(['score' => $score]);
        }
    }
}
