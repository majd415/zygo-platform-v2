<?php

namespace App\Events;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideRequestCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride_id;
    public $driver_id;

    /**
     * Create a new event instance.
     */
    public function __construct($rideId, $driverId)
    {
        $this->ride_id = $rideId;
        $this->driver_id = $driverId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.' . $this->driver_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride_id,
            'action' => 'ride.taken',
            'message' => 'This ride request has been accepted by another driver.'
        ];
    }

    public function broadcastAs(): string
    {
        return 'ride.taken';
    }
}
