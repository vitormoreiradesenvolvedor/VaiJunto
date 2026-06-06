<?php

namespace App\Events;

use App\Models\Trip;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewTripOffer implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public readonly Trip $trip) {}

    public function broadcastOn(): Channel
    {
        return new Channel('trips');
    }

    public function broadcastAs(): string
    {
        return 'NewTripOffer';
    }

    public function broadcastWith(): array
    {
        $trip    = $this->trip;
        $driver  = $trip->driver;
        $seats   = $trip->seats_total;

        return [
            'id'          => $trip->id,
            'origin'      => $trip->origin,
            'destination' => $trip->destination,
            'departs_at'  => $trip->departs_at->toIso8601String(),
            'seats_total' => $seats,
            'driver'      => [
                'name'   => $driver->name,
                'avatar' => $driver->avatar,
            ],
            'join_url' => route('dashboard'), // passageiro vai ao dashboard para entrar
        ];
    }
}
