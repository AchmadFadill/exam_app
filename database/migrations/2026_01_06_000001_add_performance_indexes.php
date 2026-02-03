<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance indexes for frequently searched/filtered columns.
     * This migration adds indexes to optimize query performance
     * when the database grows to thousands of records.
     * 
     * UPDATED: Removed references to deprecated tables and added safety checks.
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
        // Note: Additional composite index for active exams is in later migration
        Schema::table('exams', function (Blueprint $table) {
            if (!$this->hasIndex('exams', 'exams_subject_id_index')) {
                $table->index('subject_id');
            }
            if (!$this->hasIndex('exams', 'exams_teacher_id_index')) {
                $table->index('teacher_id');
            }
        });

        // NOTE: Indexes for exam_attempts and student_answers are handled
        // by migration 2026_02_03_000155_add_performance_indexes.php
    }

    /**
     * Check if an index exists on a table
     */
    private function hasIndex(string $table, string $index): bool
    {
        try {
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes($table);
            
            return isset($indexes[$index]);
        } catch (\Exception $e) {
            // If table doesn't exist or error checking, return false
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
            if ($this->hasIndex('students', 'students_classroom_id_index')) {
                $table->dropIndex(['classroom_id']);
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            if ($this->hasIndex('teachers', 'teachers_user_id_index')) {
                $table->dropIndex(['user_id']);
            }
            if ($this->hasIndex('teachers', 'teachers_subject_id_index')) {
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
            if ($this->hasIndex('questions', 'questions_subject_id_index')) {
                $table->dropIndex(['subject_id']);
            }
            if ($this->hasIndex('questions', 'questions_teacher_id_index')) {
                $table->dropIndex(['teacher_id']);
            }
            if ($this->hasIndex('questions', 'questions_type_index')) {
                $table->dropIndex(['type']);
            }
        });

        Schema::table('exams', function (Blueprint $table) {
            if ($this->hasIndex('exams', 'exams_subject_id_index')) {
                $table->dropIndex(['subject_id']);
            }
            if ($this->hasIndex('exams', 'exams_teacher_id_index')) {
                $table->dropIndex(['teacher_id']);
            }
        });
    }
};
