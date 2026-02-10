<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Performance indexes for frequently searched/filtered columns.
     * UPDATED for Laravel 12 compatibility - removed Doctrine dependency
     */
    public function up(): void
    {
        // Index for users table - role is frequently filtered during login
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_role_index')) {
                $table->index('role');
            }
        });

        // Index for students table - searched by NIS and filtered by classroom
        Schema::table('students', function (Blueprint $table) {
            if (!$this->hasIndex('students', 'students_nis_index')) {
                $table->index('nis');
            }
            if (!$this->hasIndex('students', 'students_classroom_id_index')) {
                $table->index('classroom_id');
            }
        });

        // Index for teachers table - FK columns for efficient joins
        Schema::table('teachers', function (Blueprint $table) {
            if (!$this->hasIndex('teachers', 'teachers_user_id_index')) {
                $table->index('user_id');
            }
            if (!$this->hasIndex('teachers', 'teachers_subject_id_index')) {
                $table->index('subject_id');
            }
        });

        // Index for classrooms table - ordered/filtered by level
        Schema::table('classrooms', function (Blueprint $table) {
            if (!$this->hasIndex('classrooms', 'classrooms_level_index')) {
                $table->index('level');
            }
        });

        // Index for subjects table - searched by code
        Schema::table('subjects', function (Blueprint $table) {
            if (!$this->hasIndex('subjects', 'subjects_code_index')) {
                $table->index('code');
            }
        });

        // Index for questions table - frequently filtered by subject and teacher
        Schema::table('questions', function (Blueprint $table) {
            if (!$this->hasIndex('questions', 'questions_subject_id_index')) {
                $table->index('subject_id');
            }
            if (!$this->hasIndex('questions', 'questions_teacher_id_index')) {
                $table->index('teacher_id');
            }
            if (!$this->hasIndex('questions', 'questions_type_index')) {
                $table->index('type');
            }
        });

        // Index for exams table - filtered by subject and status
        Schema::table('exams', function (Blueprint $table) {
            if (!$this->hasIndex('exams', 'exams_subject_id_index')) {
                $table->index('subject_id');
            }
            if (!$this->hasIndex('exams', 'exams_teacher_id_index')) {
                $table->index('teacher_id');
            }
        });
    }

    /**
     * Check if an index exists on a table (Laravel 12 compatible)
     */
    private function hasIndex(string $table, string $index): bool
    {
        try {
            // Use raw SQL query to check for index existence
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'sqlite') {
                $result = DB::select(
                    "SELECT COUNT(*) as count FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                    [$table, $index]
                );

                return ($result[0]->count ?? 0) > 0;
            }

            $databaseName = $connection->getDatabaseName();

            $result = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $index]
            );

            return ($result[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->hasIndex('users', 'users_role_index')) {
                $table->dropIndex(['role']);
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if ($this->hasIndex('students', 'students_nis_index')) {
                $table->dropIndex(['nis']);
            }
            if (
                $this->hasIndex('students', 'students_classroom_id_index') &&
                !$this->hasForeignOnColumn('students', 'classroom_id')
            ) {
                $table->dropIndex(['classroom_id']);
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if (
                $this->hasIndex('teachers', 'teachers_user_id_index') &&
                !$this->hasForeignOnColumn('teachers', 'user_id')
            ) {
                $table->dropIndex(['user_id']);
            }
            if (
                $this->hasIndex('teachers', 'teachers_subject_id_index') &&
                !$this->hasForeignOnColumn('teachers', 'subject_id')
            ) {
                $table->dropIndex(['subject_id']);
            }
        });

        Schema::table('classrooms', function (Blueprint $table) {
            if ($this->hasIndex('classrooms', 'classrooms_level_index')) {
                $table->dropIndex(['level']);
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            if ($this->hasIndex('subjects', 'subjects_code_index')) {
                $table->dropIndex(['code']);
            }
        });

        Schema::table('questions', function (Blueprint $table) {
            if (
                $this->hasIndex('questions', 'questions_subject_id_index') &&
                !$this->hasForeignOnColumn('questions', 'subject_id')
            ) {
                $table->dropIndex(['subject_id']);
            }
            if (
                $this->hasIndex('questions', 'questions_teacher_id_index') &&
                !$this->hasForeignOnColumn('questions', 'teacher_id')
            ) {
                $table->dropIndex(['teacher_id']);
            }
            if ($this->hasIndex('questions', 'questions_type_index')) {
                $table->dropIndex(['type']);
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if (
                $this->hasIndex('exams', 'exams_subject_id_index') &&
                !$this->hasForeignOnColumn('exams', 'subject_id')
            ) {
                $table->dropIndex(['subject_id']);
            }
            if (
                $this->hasIndex('exams', 'exams_teacher_id_index') &&
                !$this->hasForeignOnColumn('exams', 'teacher_id')
            ) {
                $table->dropIndex(['teacher_id']);
            }
        });
    }

    /**
     * Check if a foreign key exists on a given table column (Laravel 12 compatible).
     */
    private function hasForeignOnColumn(string $table, string $column): bool
    {
        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'sqlite') {
                return false;
            }

            $databaseName = $connection->getDatabaseName();

            $result = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE
                 WHERE table_schema = ? AND table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL",
                [$databaseName, $table, $column]
            );

            return ($result[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
