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
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropColumn('selected_option');
            $table->foreignId('selected_option_id')->nullable()->after('question_id')->constrained('question_options')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_answers', function (Blueprint $table) {
            $table->dropForeign(['selected_option_id']);
            $table->dropColumn('selected_option_id');
            $table->char('selected_option', 1)->nullable()->after('question_id');
        });
    }
};
