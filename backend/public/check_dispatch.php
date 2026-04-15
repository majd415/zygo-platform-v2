<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

header('Content-Type: application/json');

$latestRide = Ride::orderBy('id', 'desc')->first();
$rideRequests = [];
if ($latestRide) {
    $rideRequests = RideRequest::where('ride_id', $latestRide->id)->get();
}

$onlineDrivers = User::where('role', 'driver')->where('is_online', true)->get(['id', 'name', 'last_latitude', 'last_longitude', 'fcm_token']);

echo json_encode([
    'latest_ride' => $latestRide,
    'ride_requests' => $rideRequests,
    'online_drivers' => $onlineDrivers,
], JSON_PRETTY_PRINT);
