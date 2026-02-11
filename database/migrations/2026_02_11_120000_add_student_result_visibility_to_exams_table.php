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
        Schema::table('exams', function (Blueprint $table): void {
            $table->boolean('show_score_to_student')
                ->default(true)
                ->after('tab_tolerance');
            $table->boolean('show_answers_to_student')
                ->default(true)
                ->after('show_score_to_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn([
                'show_score_to_student',
                'show_answers_to_student',
            ]);
        });
    }
};

