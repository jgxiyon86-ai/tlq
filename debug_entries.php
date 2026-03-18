<?php
// C:\xampp\htdocs\TLQ\debug_entries.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\JournalEntry;
$entries = JournalEntry::where('challenge_id', 1)->get();
foreach ($entries as $e) {
    echo "Day " . $e->day_number . " | Date: " . $e->entry_date->toDateString() . "\n";
}
