<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideAccepted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Ride $ride) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("user.{$this->ride->passenger_id}");
    }

    public function broadcastAs(): string
    {
        return 'RideAccepted';
    }

    public function broadcastWith(): array
    {
        $ride    = $this->ride;
        $driver  = $ride->driver;
        $vehicle = $ride->vehicle ?? $driver?->vehicle;

        $rideRequest = $ride->rideRequest;

        return [
            'ride_id'        => $ride->id,
            'request_status' => 'accepted',
            'track_url'      => $rideRequest ? route('rides.track', $rideRequest) : null,
            'ride'           => [
                'id'     => $ride->id,
                'status' => $ride->status,
                'driver' => [
                    'name'   => $driver?->name,
                    'avatar' => $driver?->avatar,
                ],
                'vehicle' => $vehicle ? [
                    'model' => $vehicle->model,
                    'color' => $vehicle->color,
                    'plate' => $vehicle->plate,
                ] : null,
            ],
        ];
    }
}
