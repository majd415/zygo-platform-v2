<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Events\RideRequested;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class DispatchService
{
    /**
     * Start the dispatching process for a new ride.
     */
    public function dispatch(Ride $ride)
    {
        Log::info("Dispatching ride #{$ride->id} from {$ride->rider_id} (Type: {$ride->car_type})");
        
        // Find potential drivers
        $radius = Setting::first()->search_radius_km ?? 5;
        $drivers = $this->findNearbyDrivers(
            $ride->pickup_lat, 
            $ride->pickup_lng, 
            $radius,
            $ride->car_type
        );
// ... existing rank and take logic
    }

    public function findNearbyDrivers($lat, $lng, $radius = null, $carType = null)
    {
        if ($radius === null) {
            $radius = Setting::first()->search_radius_km ?? 5;
        }

        Log::info("SEARCHING. Radius: {$radius}km, Type: " . ($carType ?? 'All') . " at [{$lat}, {$lng}]");

        try {
            // Check if Redis is actually working and configured
            $redis = \Illuminate\Support\Facades\Redis::connection();
            if ($redis) {
                $driverIds = $redis->georadius('driver_locations', $lng, $lat, $radius, 'km', 'ASC');
                
                if (!empty($driverIds)) {
                    $query = User::whereIn('id', $driverIds)
                        ->where('role', 'driver')
                        ->where('is_online', true)
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('rides')
                                ->whereColumn('rides.driver_id', 'users.id')
                                ->whereIn('status', ['accepted', 'arrived', 'started']);
                        });

                    // Filter by service category if car_type is specified
                    if ($carType && in_array($carType, ['economy', 'comfort', 'premium'])) {
                        $query->where('service_category', $carType);
                    }

                    $drivers = $query->get();
                    Log::info("Found " . $drivers->count() . " online drivers in Redis matching criteria (category: " . ($carType ?? 'all') . ").");
                    
                    if ($drivers->isNotEmpty()) {
                        return $drivers;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ [ZYGO_DEBUG] Redis GEORADIUS Failed in findNearbyDrivers: " . $e->getMessage());
        }

        // DB Fallback
        $drivers = $this->getNearbyDriversFromDb($lat, $lng, $radius, $carType);
        Log::info("DB Fallback found " . $drivers->count() . " online drivers matching criteria.");
        return $drivers;
    }

    protected function getNearbyDriversFromDb($lat, $lng, $radius, $carType = null)
    {
        Log::info("DB Fallback check: [{$lat}, {$lng}] Radius: {$radius}km");

        // Diagnostics: check all nearby drivers
        $nearbyAll = User::where('role', 'driver')
            ->whereNotNull('last_latitude')
            ->select('users.*') 
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(last_latitude)) * cos(radians(last_longitude) - radians(?)) + sin(radians(?)) * sin(radians(last_latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->having('distance', '<=', $radius)
            ->get();

        foreach ($nearbyAll as $driver) {
            Log::info("Discovery: Driver #{$driver->id} ({$driver->name}) at {$driver->distance} km | Online: {$driver->is_online} | Status: {$driver->status} | FCM: " . ($driver->fcm_token ? 'SET' : 'MISSING'));
            
            // Check if this driver has any active rides blocking them
            $activeRides = \App\Models\Ride::where('driver_id', $driver->id)
                ->whereIn('status', ['accepted', 'arrived', 'started', 'searching'])
                ->get();
            if ($activeRides->isNotEmpty()) {
                foreach ($activeRides as $ride) {
                    Log::warning("BLOCKER: Driver #{$driver->id} has Ride #{$ride->id} with status '{$ride->status}' — cancelling stale ride.");
                    $ride->update(['status' => 'cancelled', 'cancel_reason' => 'Auto-cancelled: stale ride']);
                }
            }
        }

        // Run the actual query — Only online drivers
        $query = User::where('role', 'driver')
            ->where('is_online', true)
            ->whereNotNull('last_latitude')
            ->select('users.*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(last_latitude)) * cos(radians(last_longitude) - radians(?)) + sin(radians(?)) * sin(radians(last_latitude)))) AS distance',
                [$lat, $lng, $lat]
            )
            ->having('distance', '<=', $radius);

        // Filter by service category if car_type is specified
        if ($carType && in_array($carType, ['economy', 'comfort', 'premium'])) {
            $query->where('service_category', $carType);
        }

        return $query->orderBy('distance')->get();
    }

    public function dispatchBatch(Ride $ride, $batchSize = 5)
    {
        Log::info("🚀 [ZYGO_DEBUG] Ride dispatch batch started for Ride #{$ride->id}");
        
        // 1. Get dynamic radius from settings
        $settings = Setting::first();
        $radius = $settings->search_radius_km ?? 10;
        
        // 2. Find nearby drivers ordered by distance
        $drivers = $this->findNearbyDrivers($ride->pickup_lat, $ride->pickup_lng, $radius, $ride->car_type);
        
        Log::info("🔍 [ZYGO_DEBUG] Selecting nearest drivers. Radius: {$radius}km. Total candidates: {$drivers->count()}");

        // 3. Filter out drivers who have ALREADY been notified for this ride
        $alreadyNotifiedIds = RideRequest::where('ride_id', $ride->id)
            ->pluck('driver_id')
            ->toArray();

        $drivers = $drivers->reject(function($driver) use ($alreadyNotifiedIds) {
            return in_array($driver->id, $alreadyNotifiedIds);
        });

        // 4. Filter drivers by balance — drivers must have enough to cover platform commission
        $commissionRate = $settings->commission_rate ?? 15.00;
        $expectedCommission = ($ride->ride_price * $commissionRate) / 100;
        
        $drivers = $drivers->filter(function($driver) use ($expectedCommission) {
            $hasBalance = $driver->wallet_balance >= $expectedCommission;
            if (!$hasBalance) {
                 Log::info("⚠️ [ZYGO_DEBUG] Driver #{$driver->id} excluded: Insufficient balance ($driver->wallet_balance < $expectedCommission)");
            }
            return $hasBalance;
        });
        
        // 5. Select the top batch (Smart Selection)
        $batch = $drivers->take($batchSize);

        if ($batch->isEmpty()) {
            Log::warning("🔴 [ZYGO_DEBUG] No more suitable drivers found for Ride #{$ride->id}");
            return false;
        }

        Log::info("✅ [ZYGO_DEBUG] Drivers selected: " . $batch->count());

        foreach ($batch as $driver) {
            RideRequest::create([
                'ride_id' => $ride->id,
                'driver_id' => $driver->id,
                'status' => 'sent',
                'expires_at' => now()->addSeconds(15), // Wait 15 seconds per batch
            ]);

            // Broadcast event via WebSocket
            event(new RideRequested($ride, $driver));

            // Send Push Notification via Firebase (FCM)
            if ($driver->fcm_token) {
                \App\Services\FirebaseService::sendNotification(
                    $driver->fcm_token,
                    "New Ride Request",
                    "New request from {$ride->pickup_address}",
                    [
                        'type' => 'ride_request',
                        'ride_id' => (string) $ride->id,
                        'pickup_address' => $ride->pickup_address,
                        'destination_address' => $ride->dropoff_address,
                        'fare' => (string) $ride->ride_price,
                        'distance_text' => (string) $ride->distance_text,
                        'duration_text' => (string) $ride->duration_text,
                        'rider_name' => collect($ride->rider)->get('name', 'Passenger'),
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Rank drivers based on distance, rating, and idle time.
     */
    protected function rankDrivers($drivers, $lat, $lng)
    {
        return $drivers->sortBy(function ($driver) {
            // Priority:
            // 1. Distance (lower is better)
            // 2. Idle time (not easily implemented here without session tracking, but let's assume last_location_at)
            // 3. Rating (higher is better, so we use -rating)
            
            // Simple ranking: distance + (5 - rating)
            // Rating is 0..5. If 5, extra 0 penalty. If 0, extra 5 penalty.
            return $driver->distance + (5 - ($driver->rating ?? 4.0));
        });
    }

    /**
     * Send a ride request to a specific driver.
     */
    protected function sendRequestToDriver(Ride $ride, User $driver)
    {
        RideRequest::create([
            'ride_id' => $ride->id,
            'driver_id' => $driver->id,
            'status' => 'sent',
            'expires_at' => now()->addSeconds(10), // Each driver has 8-10 seconds
        ]);

        // Broadcast event via WebSocket
        event(new RideRequested($ride, $driver));
    }
}
