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
     */
    public function up(): void
    {
        // Index for users table - role is frequently filtered during login
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        // Index for students table - searched by NIS and filtered by classroom
        Schema::table('students', function (Blueprint $table) {
            $table->index('nis');
            $table->index('classroom_id');
        });

        // Index for teachers table - FK columns for efficient joins
        Schema::table('teachers', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('subject_id');
        });

        // Index for classrooms table - ordered/filtered by level
        Schema::table('classrooms', function (Blueprint $table) {
            $table->index('level');
        });

        // Index for subjects table - searched by code
        Schema::table('subjects', function (Blueprint $table) {
            $table->index('code');
        });

        // Index for questions table - frequently filtered by subject and teacher
        Schema::table('questions', function (Blueprint $table) {
            $table->index('subject_id');
            $table->index('teacher_id');
            $table->index('type');
        });

        // Index for exams table - filtered by subject and status
        Schema::table('exams', function (Blueprint $table) {
            $table->index('subject_id');
            $table->index('teacher_id');
        });

        // Index for exam_sessions table - high volume, frequently queried
        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->index('exam_id');
            $table->index('student_id');
            $table->index(['exam_id', 'student_id']); // Composite index for uniqueness check
        });

        // Index for exam_answers table - very high volume
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->index('exam_session_id');
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['nis']);
            $table->dropIndex(['classroom_id']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['subject_id']);
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropIndex(['level']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['code']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['teacher_id']);
            $table->dropIndex(['type']);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['teacher_id']);
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropIndex(['exam_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['exam_id', 'student_id']);
        });

        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropIndex(['exam_session_id']);
            $table->dropIndex(['question_id']);
        });
    }
};
