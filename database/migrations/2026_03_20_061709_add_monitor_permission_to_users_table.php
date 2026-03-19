<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $row) {
            $row->boolean('can_monitor_challenges')->default(false)->after('can_manage_guides');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $row) {
            $row->dropColumn('can_monitor_challenges');
        });
    }
};
