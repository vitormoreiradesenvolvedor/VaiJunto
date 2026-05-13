<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideStarted implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Ride $ride) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->ride->passenger_id}");
    }

    public function broadcastAs(): string
    {
        return 'RideStarted';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id'    => $this->ride->id,
            'request_id' => $this->ride->rideRequest?->id,
        ];
    }
}
