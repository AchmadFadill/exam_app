<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('subject_teacher')) {
            Schema::create('subject_teacher', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // Migrate existing data
        $teachers = DB::table('teachers')->whereNotNull('subject_id')->get();
        foreach ($teachers as $teacher) {
            DB::table('subject_teacher')->updateOrInsert([
                'teacher_id' => $teacher->id,
                'subject_id' => $teacher->subject_id,
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Drop old column
        Schema::table('teachers', function (Blueprint $table) {
            if (Schema::hasColumn('teachers', 'subject_id')) {
                try {
                    $table->dropForeign(['subject_id']);
                } catch (\Throwable $e) {
                    // ignore when foreign key does not exist
                }

                $table->dropColumn('subject_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_teacher');
    }
};
