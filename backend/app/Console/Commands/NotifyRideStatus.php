<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use App\Events\RideStatusUpdated;

class NotifyRideStatus extends Command
{
    protected $signature = 'ride:notify {rideId} {status}';
    protected $description = 'Broadcast ride status change and send FCM notifications to driver & rider';

    public function handle()
    {
        $rideId = $this->argument('rideId');
        $status = $this->argument('status');
        
        $ride = Ride::find($rideId);
        if (!$ride) {
            $this->error("Ride #$rideId not found");
            return 1;
        }

        // 1. Broadcast via WebSocket (Reverb)
        event(new RideStatusUpdated($ride, "ride.$status"));
        $this->info("✅ WebSocket broadcast sent for Ride #$rideId → $status");

        // 2. Send FCM push to rider
        $rider = $ride->rider;
        if ($rider && $rider->fcm_token) {
            $titles = [
                'completed' => 'Trip Completed',
                'cancelled' => 'Trip Cancelled',
                'started' => 'Trip Started',
                'arrived' => 'Driver Arrived',
            ];
            $bodies = [
                'completed' => 'Your trip has been completed by admin.',
                'cancelled' => 'Your trip has been cancelled by admin.',
                'started' => 'Your trip has started.',
                'arrived' => 'Your driver has arrived.',
            ];
            
            \App\Services\FirebaseService::sendNotification(
                $rider->fcm_token,
                $titles[$status] ?? "Ride Update",
                $bodies[$status] ?? "Your ride status has changed to $status.",
                [
                    'type' => "ride_$status",
                    'ride_id' => (string) $rideId,
                ]
            );
            $this->info("✅ FCM push sent to rider: {$rider->name}");
        }

        // 3. Send FCM push to driver
        $driver = $ride->driver;
        if ($driver && $driver->fcm_token) {
            \App\Services\FirebaseService::sendNotification(
                $driver->fcm_token,
                'Ride Update',
                "Ride #$rideId has been $status by admin.",
                [
                    'type' => "ride_$status",
                    'ride_id' => (string) $rideId,
                ]
            );
            $this->info("✅ FCM push sent to driver: {$driver->name}");
        }

        return 0;
    }
}
