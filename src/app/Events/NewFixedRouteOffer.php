<?php

namespace App\Events;

use App\Models\FixedRoute;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class NewFixedRouteOffer implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public readonly FixedRoute $fixedRoute) {}

    public function broadcastOn(): Channel
    {
        return new Channel('routes');
    }

    public function broadcastAs(): string
    {
        return 'NewFixedRouteOffer';
    }

    public function broadcastWith(): array
    {
        $fr     = $this->fixedRoute;
        $driver = $fr->driver;

        return [
            'id'              => $fr->id,
            'origin'          => $fr->origin,
            'destination'     => $fr->destination,
            'departure_time'  => $fr->departure_time,
            'days_label'      => $fr->days_label,
            'available_seats' => $fr->available_seats,
            'driver'          => [
                'id'     => $driver->id,
                'name'   => $driver->name,
                'avatar' => $driver->avatar,
            ],
        ];
    }
}
