<?php
// C:\xampp\htdocs\TLQ\check_user_licenses.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\License;

$user = User::firstWhere('id', 2);
if (!$user) {
    echo "User 2 not found\n";
    exit;
}

echo "--- User: " . $user->email . " ---\n";
$licenses = License::where('activated_by', 2)->with('series')->get();
if ($licenses->isEmpty()) {
    echo "No licenses found for User 2\n";
}
foreach ($licenses as $l) {
    echo "Series: " . ($l->series->name ?? 'N/A') . " (ID: " . $l->series_id . ") | Key: " . $l->license_key . " | Status: " . ($l->is_activated ? 'Active' : 'Inactive') . "\n";
}
