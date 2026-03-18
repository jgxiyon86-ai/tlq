<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Challenge;

$email = 'jgx.iyon86@gmail.com';
$user = User::where('email', $email)->first();

if (!$user) {
    echo "User $email not found.\n";
    exit;
}

echo "User ID: " . $user->id . "\n";
echo "Current Server Time: " . now() . "\n";
foreach ($user->challenges()->with('journalEntries')->get() as $c) {
    echo "--- Challenge ID: " . $c->id . " ---\n";
    echo "Series ID: " . $c->series_id . "\n";
    echo "Total Days: " . $c->total_days . "\n";
    echo "Current Day (db): " . $c->current_day . "\n";
    echo "Is Completed: " . ($c->is_completed ? 'YES' : 'NO') . "\n";
    echo "Started At: " . $c->started_at . "\n";
    
    $startDate = $c->started_at ?? $c->created_at;
    $targetDay = $startDate->copy()->startOfDay()->diffInDays(now()->startOfDay()) + 1;
    echo "Calculated Target Day (Real Life): " . $targetDay . "\n";
    
    $filled = $c->journalEntries->where('is_completed', true)->count();
    echo "Journal Entries Filled: " . $filled . "\n";
    
    // Check entry for day 1
    $day1 = $c->journalEntries->where('day_number', 1)->first();
    echo "Day 1 Entry Date: " . ($day1->entry_date ?? 'N/A') . " | Completed: " . ($day1 && $day1->is_completed ? 'YES' : 'NO') . "\n";
}
