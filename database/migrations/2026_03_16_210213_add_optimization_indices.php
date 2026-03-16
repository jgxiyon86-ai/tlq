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
        Schema::table('licenses', function (Blueprint $table) {
            // Compound index for dashboard queries (which jars does this user own?)
            $table->index(['activated_by', 'is_activated', 'series_id'], 'idx_user_license_active');
            $table->index('activated_at');
        });

        Schema::table('challenges', function (Blueprint $table) {
            // For listing user's active/completed challenges
            $table->index(['user_id', 'is_completed']);
            $table->index('series_id');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            // For checking rolls and daily progress
            $table->index(['challenge_id', 'entry_date']);
            $table->index('created_at'); // Critical for Monitoring Admin Screen
            $table->index('is_completed');
        });
        
        Schema::table('contents', function (Blueprint $table) {
            // For random rolling within a series
            $table->index('series_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropIndex('idx_user_license_active');
            $table->dropIndex(['activated_at']);
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_completed']);
            $table->dropIndex(['series_id']);
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['challenge_id', 'entry_date']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['is_completed']);
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropIndex(['series_id']);
        });
    }
};
