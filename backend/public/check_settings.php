<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

header('Content-Type: application/json');

$settings = Setting::first();

echo json_encode(['settings' => $settings], JSON_PRETTY_PRINT);
