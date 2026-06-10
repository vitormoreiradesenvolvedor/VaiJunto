<?php

namespace App\Events;

use App\Models\FixedRoute;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class FixedRoutePaused implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(
        public readonly FixedRoute $fixedRoute,
        public readonly User $passenger,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.{$this->passenger->id}");
    }

    public function broadcastAs(): string
    {
        return 'FixedRoutePaused';
    }

    public function broadcastWith(): array
    {
        return [
            'route_id'    => $this->fixedRoute->id,
            'origin'      => $this->fixedRoute->origin,
            'destination' => $this->fixedRoute->destination,
        ];
    }
}
