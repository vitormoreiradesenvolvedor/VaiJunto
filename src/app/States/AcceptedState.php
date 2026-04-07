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
        $ride->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function complete(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona aceita precisa ser iniciada antes de ser concluída.'
        );
    }

    public function cancel(Ride $ride): void
    {
        $ride->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
