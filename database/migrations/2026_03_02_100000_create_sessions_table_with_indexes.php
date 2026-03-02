<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NEXA Performance Fix – Database Session Driver
 *
 * Creates the standard Laravel `sessions` table required when SESSION_DRIVER=database.
 *
 * WHY THIS MATTERS FOR 400 CONCURRENT STUDENTS
 * ─────────────────────────────────────────────
 * • File-based sessions (the default) use flock() to read/write session files.
 *   Under load, PHP-FPM workers queue up waiting for the lock, causing cascading
 *   request timeouts and the "Login Failures" symptom.
 *
 * • Database sessions replace file I/O with indexed DB rows.  MySQL/PostgreSQL
 *   handles row-level locking far more efficiently than the OS filesystem for
 *   hundreds of concurrent writers.
 *
 * INDEXES
 * ───────
 * • user_id   – enables fast lookup of all sessions belonging to a user
 *               (useful for "logout everywhere" and teacher monitoring).
 * • last_activity – used by the garbage-collector sweep (SESSION_LOTTERY).
 *
 * AFTER RUNNING THIS MIGRATION
 * ────────────────────────────
 * Set the following in your .env:
 *   SESSION_DRIVER=database
 *   SESSION_LIFETIME=180          # 3 h – longer than the longest exam
 *   SESSION_ENCRYPT=false         # encryption adds overhead; app-level auth is sufficient
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            // Table already created by a previous migration or artisan session:table.
            // Only add the indexes if they are missing to avoid duplicates.
            Schema::table('sessions', function (Blueprint $table) {
                // user_id index – critical for per-user lookups under load
                if (!$this->indexExists('sessions', 'sessions_user_id_index')) {
                    $table->index('user_id');
                }
                // last_activity index – used by garbage collector
                if (!$this->indexExists('sessions', 'sessions_last_activity_index')) {
                    $table->index('last_activity');
                }
            });
            return;
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();

            // Nullable because guest (unauthenticated) sessions have no user.
            $table->foreignId('user_id')->nullable()->index();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Stores the serialised session payload (base64-encoded).
            $table->longText('payload');

            // Unix timestamp – used by the session garbage collector.
            $table->integer('last_activity')->unsigned()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }

    /** Helper: check whether a named index already exists. */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $dbName     = $connection->getDatabaseName();
        $driver     = $connection->getDriverName();

        if ($driver === 'pgsql') {
            return (bool) $connection
                ->table('pg_indexes')
                ->where('tablename', $table)
                ->where('indexname', $indexName)
                ->exists();
        }

        // MySQL / MariaDB
        $indexes = $connection
            ->select('SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$indexName]);

        return count($indexes) > 0;
    }
};
