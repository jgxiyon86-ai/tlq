<?php
// C:\xampp\htdocs\TLQ\repair_entries.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Challenge;
use App\Models\JournalEntry;

$challenges = Challenge::all();
foreach ($challenges as $c) {
    echo "Processing Challenge ID: " . $c->id . " (User " . $c->user_id . ")\n";
    $startDate = $c->started_at ?? $c->created_at;
    $total = (int)$c->total_days;
    
    $createdCount = 0;
    for ($i = 1; $i <= $total; $i++) {
        $exists = JournalEntry::where('challenge_id', $c->id)
            ->where('day_number', $i)
            ->exists();
            
        if (!$exists) {
            JournalEntry::create([
                'user_id' => $c->user_id,
                'challenge_id' => $c->id,
                'day_number' => $i,
                'entry_date' => $startDate->copy()->addDays($i-1)->toDateString(),
                'is_completed' => false,
            ]);
            $createdCount++;
        }
    }
    echo "Created $createdCount missing entries.\n";
}
