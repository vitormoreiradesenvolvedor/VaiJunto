<?php

namespace App\Events;

use App\Models\Ride;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RideAccepted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Ride $ride) {}
}
