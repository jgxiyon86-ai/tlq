<?php
// C:\xampp\htdocs\TLQ\debug_api.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Challenge;

echo "--- All Challenges ---\n";
$challenges = Challenge::with('series')->get();
if ($challenges->isEmpty()) {
    echo "No challenges in DB\n";
}
foreach ($challenges as $c) {
    echo "ID: " . $c->id . " | User ID: " . $c->user_id . " | Series: " . ($c->series->name ?? 'N/A') . " | Comp: " . ($c->is_completed ? '1' : '0') . "\n";
}

echo "\n--- Today logic check ---\n";
$today = now()->toDateString();
echo "Server Today: $today\n";
foreach ($challenges as $c) {
    $entry = $c->journalEntries()->where('entry_date', $today)->first();
    echo "ID " . $c->id . " Today Entry: " . ($entry ? "Day ".$entry->day_number : "None") . "\n";
}
