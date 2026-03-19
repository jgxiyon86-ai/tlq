<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role: null = User biasa, 'admin' = Admin, 'super_admin' = Super Admin
            $table->string('role')->nullable()->after('is_admin');
            
            // permission flags for admin role
            $table->boolean('can_manage_licenses')->default(false)->after('role');
            $table->boolean('can_manage_contents')->default(false)->after('can_manage_licenses');
            $table->boolean('can_manage_guides')->default(false)->after('can_manage_contents');
        });

        // Migrate existing is_admin = true users to role = 'super_admin'
        DB::table('users')->where('is_admin', true)->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'can_manage_licenses', 'can_manage_contents', 'can_manage_guides']);
        });
    }
};
