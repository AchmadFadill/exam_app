<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_classes');
    }
}
