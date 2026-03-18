<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Challenge;

echo "--- DUMP SEMUA TANTANGAN DI DB ---\n";
$all = Challenge::all();
if ($all->isEmpty()) {
    echo "Database Kosong Melompong.\n";
}
foreach ($all as $c) {
    $owner = User::find($c->user_id);
    echo "ID: $c->id | Email: " . ($owner->email ?? 'Anonim') . " | Seri: $c->series_id | Selesai: " . ($c->is_completed ? 'YA' : 'TIDAK') . " | Tgl Dibuat: $c->created_at\n";
}
