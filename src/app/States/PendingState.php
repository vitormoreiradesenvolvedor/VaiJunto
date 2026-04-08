<?php

namespace App\States;

use App\Models\Ride;
use App\Events\RideAccepted;
use App\Exceptions\InvalidStateTransitionException;

class PendingState implements RideState
{
    public function accept(Ride $ride): void
    {
        $ride->status = 'accepted';
        if ($ride->exists) {
            $ride->save();
        }
        event(new RideAccepted($ride));
    }

    public function start(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona pendente não pode ser iniciada sem antes ser aceita.'
        );
    }

    public function complete(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona pendente não pode ser concluída.'
        );
    }

    public function cancel(Ride $ride): void
    {
        $ride->status = 'cancelled';
        $ride->cancelled_at = now();
        if ($ride->exists) {
            $ride->save();
        }
    }
}
