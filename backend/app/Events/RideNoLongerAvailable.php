<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RideNoLongerAvailable implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride_id;
    public $driver_id;

    public function __construct($rideId, $driverId)
    {
        $this->ride_id = $rideId;
        $this->driver_id = $driverId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.' . $this->driver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rideNoLongerAvailable';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride_id,
            'message' => 'This ride has been accepted by another driver',
        ];
    }
}
