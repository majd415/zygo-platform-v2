<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Ride;
use App\Models\Setting;

header('Content-Type: application/json');

$ride = Ride::orderBy('id', 'desc')->first();
$driver = User::find(6);
$settings = Setting::first();
$radius = $settings ? $settings->search_radius_km : 5.0;

if (!$ride || !$driver) {
    echo json_encode(['error' => 'Ride or Driver not found']);
    exit;
}

function getDistance($lat1, $lon1, $lat2, $lon2) {
    if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 999999;
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    return $miles * 1.609344;
}

$distance = getDistance($ride->pickup_lat, $ride->pickup_lng, $driver->last_latitude, $driver->last_longitude);

echo json_encode([
    'ride_id' => $ride->id,
    'ride_pickup' => ['lat' => $ride->pickup_lat, 'lng' => $ride->pickup_lng],
    'driver_location' => ['lat' => $driver->last_latitude, 'lng' => $driver->last_longitude],
    'distance_km' => $distance,
    'radius_km' => $radius,
    'driver_fcm_token' => $driver->fcm_token,
    'driver_is_online' => $driver->is_online,
], JSON_PRETTY_PRINT);
