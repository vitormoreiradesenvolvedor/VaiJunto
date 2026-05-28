<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class RideCancelledByPassenger implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public readonly Ride $ride) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->ride->driver_id}");
    }

    public function broadcastAs(): string
    {
        return 'RideCancelledByPassenger';
    }

    public function broadcastWith(): array
    {
        return [
            'ride_id'        => $this->ride->id,
            'passenger_name' => $this->ride->passenger?->name,
        ];
    }
}
