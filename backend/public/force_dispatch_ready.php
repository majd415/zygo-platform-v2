<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Redis;

header('Content-Type: application/json');

// Force driver online
$driver = User::find(6);
if ($driver) {
    $driver->is_online = 1;
    $driver->save();
    
    // Add to Redis GEO as well
    try {
        Redis::geoadd('driver_locations', $driver->last_longitude, $driver->last_latitude, $driver->id);
    } catch (\Exception $e) {}
}

// Increase search radius
$settings = Setting::first();
if ($settings) {
    $settings->search_radius_km = 500.0;
    $settings->save();
}

echo json_encode(['status' => 'success', 'message' => 'Driver forced online and radius set to 500km']);
