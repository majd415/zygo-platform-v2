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

class RideAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride;
    public $driver;

    public function __construct(Ride $ride, User $driver)
    {
        $this->ride = $ride->load('driver');
        $this->driver = $driver;
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
        return 'driverAcceptedRide';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => 'driverAcceptedRide',
            'ride_id' => $this->ride->id,
            'ride_code' => $this->ride->ride_code ?? '---',
            'fare' => round($this->ride->ride_price),
            'driver' => [
                'name' => $this->driver->name,
                'phone' => $this->driver->phone,
                'photo' => $this->driver->avatar_url,
                'rating' => $this->driver->rating ?? 4.93,
                'latitude' => $this->driver->last_latitude,  // Current location
                'longitude' => $this->driver->last_longitude, // Current location
                'vehicle' => [
                    'model' => $this->driver->driverDocument?->car_model ?? 'Standard',
                    'plate' => $this->driver->driverDocument?->car_plate ?? 'N/A',
                    'color' => $this->driver->driverDocument?->car_color ?? 'White',
                ],
            ],
        ];
    }
}
