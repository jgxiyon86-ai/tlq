<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Challenge;

echo "--- Global Challenges Data ---\n";
foreach (User::all() as $u) {
    $active = $u->challenges()->where('is_completed', false)->get();
    if ($active->isNotEmpty()) {
        echo "User: " . $u->email . " (ID: $u->id)\n";
        foreach ($active as $c) {
            echo " - ID: " . $c->id . " | Series ID: " . $c->series_id . " | Started: " . $c->started_at . "\n";
        }
    }
}
