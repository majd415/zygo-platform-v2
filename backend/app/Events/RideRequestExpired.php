<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RideRequestExpired implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride;

    public function __construct(Ride $ride)
    {
        $this->ride = $ride;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ride.' . $this->ride->id),
            new PrivateChannel('user.' . $this->ride->rider_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rideRequestExpired';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'status' => 'no_driver_found',
            'message' => 'No drivers were found for your request.',
        ];
    }
}
