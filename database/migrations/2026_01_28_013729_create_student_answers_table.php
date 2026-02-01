<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('answer')->nullable(); // For essay questions
            $table->char('selected_option', 1)->nullable(); // A, B, C, D, E for multiple choice
            $table->boolean('is_correct')->nullable();
            $table->integer('score_awarded')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->timestamps();
            
            // One answer per question per attempt
            $table->unique(['exam_attempt_id', 'question_id']);
            
            // Indexes for grading queries
            $table->index('exam_attempt_id');
            $table->index(['exam_attempt_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
