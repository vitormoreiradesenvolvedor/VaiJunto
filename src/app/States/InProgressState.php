<?php

namespace App\States;

use App\Models\Ride;
use App\Events\RideCompleted;
use App\Exceptions\InvalidStateTransitionException;

class InProgressState implements RideState
{
    public function accept(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona em andamento não pode ser aceita novamente.'
        );
    }

    public function start(Ride $ride): void
    {
        throw new InvalidStateTransitionException(
            'Carona já está em andamento.'
        );
    }

    public function complete(Ride $ride): void
    {
        $ride->status = 'completed';
        $ride->completed_at = now();
        if ($ride->exists) {
            $ride->save();
        }
        event(new RideCompleted($ride));
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
