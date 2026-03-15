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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
            $table->integer('total_days')->default(40);
            $table->integer('current_day')->default(1);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->integer('day_number');
            $table->date('entry_date');
            
            // Before Section (Pagi)
            $table->text('before_pesan')->nullable();
            $table->text('before_perasaan')->nullable();
            $table->text('before_action')->nullable();
            
            // After Section (Sore)
            $table->text('after_berhasil')->nullable();
            $table->text('after_perubahan')->nullable();
            $table->text('after_perasaan')->nullable();

            $table->boolean('is_completed')->default(false); // True jika before & after sudah diisi
            $table->timestamps();
            
            $table->unique(['challenge_id', 'day_number']); // One entry per day per challenge
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('challenges');
    }
};
