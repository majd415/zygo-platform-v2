<?php

namespace App\Listeners;

use App\Events\RideAccepted;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendRideAcceptedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RideAccepted $event): void
    {
        $ride = $event->ride;
        $driver = $event->driver;
        $rider = $ride->rider;

        if (!$rider || !$rider->fcm_token) {
            Log::info("Rider or FCM token not found for ride {$ride->id}");
            return;
        }

        $title = "Captain Accepted!";
        $body = "Captain {$driver->name} is on the way to pick you up.";

        FirebaseService::sendNotification(
            $rider->fcm_token,
            $title,
            $body,
            [
                'type' => 'ride_accepted',
                'ride_id' => (string) $ride->id,
                'action' => 'ride.accepted',
                'driver_name' => (string) $driver->name,
                'driver_photo' => (string) $driver->avatar_url,
                'car_model' => (string) ($driver->driverDocument->car_model ?? 'Standard'),
                'car_plate' => (string) ($driver->driverDocument->car_plate ?? 'N/A'),
            ]
        );
    }
}
