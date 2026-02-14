<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exams', 'is_published')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->boolean('is_published')->default(false)->after('show_answers_to_student');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exams', 'is_published')) {
            Schema::table('exams', function (Blueprint $table) {
                $table->dropColumn('is_published');
            });
        }
    }
};
