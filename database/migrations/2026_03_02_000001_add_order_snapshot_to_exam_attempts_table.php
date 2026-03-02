<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NEXA Performance Fix – Snapshot Randomization
 *
 * Adds two JSON columns to exam_attempts:
 *   - question_order : ordered array of question IDs as they were presented
 *   - options_order  : associative JSON map { "question_id": [option_id, …] }
 *
 * These are populated ONCE when the student clicks "Start", ensuring a stable
 * ordering across every Livewire hydration / page-reload, eliminating
 * "Randomization Drift" (mismatched text, images, options).
 *
 * Also adds last_seen_at which is used by the heartbeat endpoint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            // Snapshot of question IDs in the order shown to this student.
            // Example: [12, 7, 3, 19, 45]
            $table->json('question_order')->nullable()->after('tab_switches');

            // Per-question option ID ordering.
            // Example: { "12": [44,41,42,43], "7": [28,27,29,30] }
            $table->json('options_order')->nullable()->after('question_order');

            // Heartbeat timestamp – updated by the active-user polling ping.
            // Added conditionally so re-running migration on live DB is safe.
            if (!Schema::hasColumn('exam_attempts', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumnIfExists('question_order');
            $table->dropColumnIfExists('options_order');
            // Only drop last_seen_at if it was added by this migration
            // (it may have been added by an earlier migration in some deployments)
        });
    }
};
