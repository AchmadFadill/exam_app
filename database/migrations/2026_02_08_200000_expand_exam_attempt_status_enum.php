<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE exam_attempts
            MODIFY status ENUM('in_progress', 'submitted', 'graded', 'abandoned', 'completed', 'ongoing', 'timed_out')
            NOT NULL DEFAULT 'in_progress'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE exam_attempts
            MODIFY status ENUM('in_progress', 'submitted', 'graded', 'abandoned')
            NOT NULL DEFAULT 'in_progress'
        ");
    }
};
