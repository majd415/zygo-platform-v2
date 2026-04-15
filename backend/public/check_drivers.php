<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

header('Content-Type: application/json');

$drivers = User::where('role', 'driver')->get(['id', 'name', 'is_online', 'last_latitude', 'last_longitude']);

echo json_encode(['drivers' => $drivers], JSON_PRETTY_PRINT);
