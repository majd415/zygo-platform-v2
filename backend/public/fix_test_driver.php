<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

try {
    $user = User::find(11);
    if ($user) {
        $user->role = 'driver';
        $user->status = 'approved';
        // Set a dummy location in Damascus for testing search
        $user->last_latitude = 33.5138;
        $user->last_longitude = 36.2765;
        $user->is_online = 1;
        $user->save();
        
        // Add to Redis
        \Illuminate\Support\Facades\Redis::geoadd('driver_locations', $user->last_longitude, $user->last_latitude, $user->id);
        
        echo "User 11 updated to approved driver with location.\n";
    } else {
        echo "User 11 not found.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
