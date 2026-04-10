<?php

namespace App\Events;

use App\Models\RideRequest;
use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class TripRequestReceived implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public readonly Trip $trip,
        public readonly RideRequest $rideRequest,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("user.{$this->trip->driver_id}");
    }

    public function broadcastAs(): string
    {
        return 'TripRequestReceived';
    }

    public function broadcastWith(): array
    {
        return [
            'trip_id'      => $this->trip->id,
            'request_id'   => $this->rideRequest->id,
            'origin'       => $this->trip->origin,
            'destination'  => $this->trip->destination,
            'passenger'    => [
                'name'   => $this->rideRequest->passenger->name,
                'avatar' => $this->rideRequest->passenger->avatar,
            ],
            'trip_url'     => route('trips.show', $this->trip),
        ];
    }
}
