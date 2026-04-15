<?php

namespace App\Events;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RideRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride;
    public $driver;

    public function __construct(Ride $ride, User $driver)
    {
        $this->ride = $ride->load('rider');
        $this->driver = $driver;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.' . $this->driver->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ride.requested';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride->id,
            'pickup_address' => $this->ride->pickup_address,
            'pickup_lat' => $this->ride->pickup_lat,
            'pickup_lng' => $this->ride->pickup_lng,
            'destination_address' => $this->ride->dropoff_address,
            'rider_name' => $this->ride->rider->name,
            'rider_photo' => $this->ride->rider->avatar_url,
            'distance_text' => $this->ride->distance_text ?? '---',
            'duration_text' => $this->ride->duration_text ?? '---',
            'fare' => $this->ride->ride_price,
            'expires_at' => now()->addSeconds(10)->toIso8601String(),
        ];
    }
}
