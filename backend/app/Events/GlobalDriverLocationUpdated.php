<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GlobalDriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driver_id;
    public $latitude;
    public $longitude;
    public $is_online;

    public function __construct(User $driver)
    {
        $this->driver_id = $driver->id;
        $this->latitude = $driver->last_latitude;
        $this->longitude = $driver->last_longitude;
        $this->is_online = $driver->is_online;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'driver.global_location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'driver_id' => $this->driver_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_online' => $this->is_online,
        ];
    }
}
