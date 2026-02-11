<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropForeign(['selected_option_id']);
            $table->foreign('selected_option_id')
                ->references('id')
                ->on('question_options')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropForeign(['selected_option_id']);
            $table->foreign('selected_option_id')
                ->references('id')
                ->on('question_options')
                ->onDelete('cascade');
        });
    }
};

