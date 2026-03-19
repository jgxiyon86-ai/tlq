<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Untuk blokir permanent manual
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('role');
            $table->integer('failed_login_count')->default(0)->after('is_blocked');
        });

        // Catatan percobaan login
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->boolean('is_successful')->default(false);
            $table->timestamps();
        });

        // Log Aktivitas Admin (Saran saya untuk keamanan)
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // misal: CREATE_LICENSE, UPDATE_USER
            $table->string('target_type')->nullable(); // misal: License, User
            $table->string('target_id')->nullable();
            $table->text('old_data')->nullable();
            $table->text('new_data')->nullable();
            $table->string('ip_address');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('login_attempts');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'failed_login_count']);
        });
    }
};
