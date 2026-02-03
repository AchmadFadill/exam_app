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
        // Add composite index for active exam lookups
        Schema::table('exams', function (Blueprint $table) {
            if (!$this->hasIndex('exams', 'exams_active_lookup')) {
                $table->index(['status', 'date', 'start_time', 'end_time'], 'exams_active_lookup');
            }
        });

        // Add indexes for exam_attempts table (new table, likely no existing indexes)
        Schema::table('exam_attempts', function (Blueprint $table) {
            if (!$this->hasIndex('exam_attempts', 'attempts_lookup')) {
                $table->index(['exam_id', 'student_id', 'status'], 'attempts_lookup');
            }
            if (!$this->hasIndex('exam_attempts', 'exam_attempts_submitted_at_index')) {
                $table->index('submitted_at');
            }
        });

        // Add indexes for student_answers table
        Schema::table('student_answers', function (Blueprint $table) {
            if (!$this->hasIndex('student_answers', 'student_answers_exam_attempt_id_index')) {
                $table->index('exam_attempt_id');
            }
            if (!$this->hasIndex('student_answers', 'student_answers_question_id_index')) {
                $table->index('question_id');
            }
        });
    }

    /**
     * Check if an index exists (Laravel 12 compatible)
     */
    private function hasIndex(string $table, string $index): bool
    {
        try {
            $connection = Schema::getConnection();
            $databaseName = $connection->getDatabaseName();
            
            $result = \Illuminate\Support\Facades\DB::select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $index]
            );
            
            return $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('exams_active_lookup');
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex('attempts_lookup');
            $table->dropIndex(['submitted_at']);
        });

        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropIndex(['exam_attempt_id']);
            $table->dropIndex(['question_id']);
        });
    }
};
