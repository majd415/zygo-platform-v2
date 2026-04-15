<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ride;
use App\Models\User;
use App\Jobs\DispatchRideJob;

try {
    $rider = User::where('role', 'rider')->first();
    if (!$rider) {
        $rider = User::create(['name' => 'Test Rider', 'email' => 'rider_test@example.com', 'password' => bcrypt('password'), 'role' => 'rider']);
    }

    $ride = Ride::create([
        'rider_id' => $rider->id,
        'pickup_address' => 'Damascus, Syria',
        'pickup_lat' => 33.5138,
        'pickup_lng' => 36.2765,
        'dropoff_address' => 'Damascus Station',
        'dropoff_lat' => 33.5100,
        'dropoff_lng' => 36.2700,
        'ride_price' => 5000,
        'status' => 'searching',
    ]);

    echo "Ride #" . $ride->id . " created. Dispatching...\n";
    echo "Log file should be: " . storage_path('logs/laravel.log') . "\n";
    Log::error("TEST LOG ENTRY AT " . date('Y-m-d H:i:s'));
    file_put_contents(storage_path('logs/laravel.log'), "[" . date('Y-m-d H:i:s') . "] simulation.INFO: MANUAL WRITE TEST\n", FILE_APPEND);
    
    // Dispatch immediately (simulate job)
    dispatch(new DispatchRideJob($ride));
    
    echo "Job dispatched. Check laravel.log for 'Processing DispatchRideJob' and 'New Ride Request' FCM logs.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
