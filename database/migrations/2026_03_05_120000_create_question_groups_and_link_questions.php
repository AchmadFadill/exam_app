<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();

            $table->index(['teacher_id', 'subject_id']);
            $table->unique(['teacher_id', 'subject_id', 'title'], 'uq_question_groups_scope_title');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('question_group_id')
                ->nullable()
                ->after('subject_id')
                ->constrained('question_groups')
                ->nullOnDelete();
            $table->index('question_group_id');
        });

        $groups = DB::table('questions')
            ->select('teacher_id', 'subject_id', 'title')
            ->whereNotNull('subject_id')
            ->groupBy('teacher_id', 'subject_id', 'title')
            ->get();

        foreach ($groups as $group) {
            $groupId = DB::table('question_groups')->insertGetId([
                'teacher_id' => $group->teacher_id,
                'subject_id' => $group->subject_id,
                'title' => $group->title ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('questions')
                ->where('teacher_id', $group->teacher_id)
                ->where('subject_id', $group->subject_id)
                ->where('title', $group->title)
                ->update(['question_group_id' => $groupId]);
        }
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_group_id');
        });

        Schema::dropIfExists('question_groups');
    }
};

