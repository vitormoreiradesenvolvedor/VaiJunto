<?php

namespace App\States;

use App\Models\Ride;

interface RideState
{
    public function accept(Ride $ride): void;
    public function start(Ride $ride): void;
    public function complete(Ride $ride): void;
    public function cancel(Ride $ride): void;
}
