<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride_id;
    public $latitude;
    public $longitude;
    public $heading;

    public function __construct($ride_id, $latitude, $longitude, $heading = 0)
    {
        $this->ride_id = $ride_id;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->heading = $heading;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ride.' . $this->ride_id),
            new Channel('admin'), // Public for now, or use PrivateChannel if authenticated
        ];
    }

    public function broadcastAs(): string
    {
        return 'driverLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id' => $this->ride_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bearing' => $this->heading,
        ];
    }

}
