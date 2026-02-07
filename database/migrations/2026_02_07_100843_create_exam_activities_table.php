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
        Schema::create('exam_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_attempt_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // 'fullscreen_exit', 'tab_switch', 'device_change', etc.
            $table->string('severity')->default('warning'); // 'info', 'warning', 'critical'
            $table->text('message')->nullable();
            $table->json('metadata')->nullable(); // browser info, ip, etc.
            $table->timestamps();
            
            // Index for faster querying on dashboard
            $table->index(['exam_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_activities');
    }
};
