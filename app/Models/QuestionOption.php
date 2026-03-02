<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['question_id', 'label', 'text', 'image_path', 'is_correct'];

    protected $casts = ['is_correct' => 'boolean'];

    protected static function booted(): void
    {
        static::deleting(function (QuestionOption $option): void {
            if ($option->isForceDeleting() && $option->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($option->image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($option->image_path);
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) : null;
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
