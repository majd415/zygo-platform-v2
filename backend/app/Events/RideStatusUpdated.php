<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RideStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ride;
    public $action;

    public function __construct(Ride $ride, $action)
    {
        $this->ride = $ride;
        $this->action = $action;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ride.' . $this->ride->id),
            new PrivateChannel('user.' . $this->ride->rider_id),
            new PrivateChannel('user.' . $this->ride->driver_id),
            new Channel('admin'),
        ];
    }

    public function broadcastAs(): string
    {
        $statusMapping = [
            'accepted' => 'driverAcceptedRide',
            'arrived' => 'driverArrived',
            'started' => 'rideStarted',
            'completed' => 'rideCompleted',
            'cancelled' => 'rideCancelled',
        ];

        return $statusMapping[$this->ride->status] ?? 'rideStatusUpdated';
    }


    public function broadcastWith(): array
    {
        $this->ride->load('rider', 'driver');
        
        $payload = [
            'ride_id' => $this->ride->id,
            'status' => $this->ride->status,
            'action' => $this->action,
            'ride_code' => $this->ride->ride_code ?? '---',
            'cancel_reason' => $this->ride->cancel_reason,
            'ride' => array_merge($this->ride->toArray(), [
                'rider_name' => $this->ride->rider->name ?? 'Guest',
                'driver_name' => $this->ride->driver->name ?? 'Searching...',
            ]),
        ];

        // Standardize driver info if available (consistent with RideAccepted)
        if ($this->ride->driver) {
            $driverDoc = \App\Models\DriverDocument::where('user_id', $this->ride->driver_id)->first();
            $payload['driver'] = [
                'name' => $this->ride->driver->name,
                'phone' => $this->ride->driver->phone,
                'photo' => $this->ride->driver->avatar_url,
                'rating' => $this->ride->driver->rating ?? 4.93,
                'vehicle' => [
                    'model' => $driverDoc?->car_model ?? 'Standard',
                    'plate' => $driverDoc?->car_plate ?? 'N/A',
                    'color' => $driverDoc?->car_color ?? 'White',
                ],
            ];
        }

        return $payload;
    }
}
