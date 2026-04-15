<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Redis;

header('Content-Type: application/json');

try {
    $drivers = Redis::zrange('driver_locations', 0, -1);
    $locations = [];
    foreach ($drivers as $id) {
        $pos = Redis::geopos('driver_locations', $id);
        $locations[$id] = $pos;
    }
    echo json_encode(['driver_locations' => $locations], JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT);
}
