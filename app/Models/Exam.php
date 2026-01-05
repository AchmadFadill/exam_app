<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id', 'subject_id', 'name', 'date', 'start_time', 'end_time',
        'duration_minutes', 'token', 'passing_grade', 'default_score',
        'shuffle_questions', 'shuffle_answers', 'tab_tolerance', 'status',
    ];

    protected $casts = [
        'date' => 'date',
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'exam_classes');
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions')
            ->withPivot('order', 'score')
            ->orderBy('exam_questions.order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public static function generateToken(): string
    {
        do {
            $token = strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
