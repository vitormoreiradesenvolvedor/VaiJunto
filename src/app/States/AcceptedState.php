<?php

namespace App\States;

use App\Models\Ride;
use App\Exceptions\InvalidStateTransitionException;

class AcceptedState implements RideState
{
    public function accept(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona já foi aceita anteriormente.'
        );
    }

    public function start(Ride $ride): void
    {
        $ride->status = 'in_progress';
        $ride->started_at = now();
        if ($ride->exists) {
            $ride->save();
        }
    }

    public function complete(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona aceita precisa ser iniciada antes de ser concluída.'
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
