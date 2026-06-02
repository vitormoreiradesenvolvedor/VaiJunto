<?php

namespace App\Events;

use App\Models\RideRequest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewRideRequestForDriver implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public readonly RideRequest $rideRequest,
        public readonly User $driver,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("user.{$this->driver->id}");
    }

    public function broadcastAs(): string
    {
        return 'NewRideRequestForDriver';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->rideRequest->id,
            'passenger_id'   => $this->rideRequest->passenger_id,
            'origin'         => $this->rideRequest->origin,
            'destination'    => $this->rideRequest->destination,
            'scheduled_for'  => $this->rideRequest->scheduled_for->toIso8601String(),
            'seats_needed'   => $this->rideRequest->seats_needed,
            'trip_id'        => $this->rideRequest->trip_id,
            'fixed_route_id' => $this->rideRequest->fixed_route_id,
            'passenger'      => [
                'name'          => $this->rideRequest->passenger->name,
                'avatar'        => $this->rideRequest->passenger->avatar,
                'avg_stars'     => round($this->rideRequest->passenger->ratingsReceived()->avg('stars') ?? 0, 1),
                'total_ratings' => $this->rideRequest->passenger->ratingsReceived()->count(),
            ],
        ];
    }
}
