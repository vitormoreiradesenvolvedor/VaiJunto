<?php

namespace App\Providers;

use App\Events\RideAccepted;
use App\Events\RideCompleted;
use App\Listeners\AwardPointsOnRideCompleted;
use App\Listeners\NotifyPassengerOnRideAccepted;
use App\Listeners\NotifyPassengerOnRideCompleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RideCompleted::class => [
            AwardPointsOnRideCompleted::class,
            NotifyPassengerOnRideCompleted::class,
        ],
        RideAccepted::class => [
            NotifyPassengerOnRideAccepted::class,
        ],
    ];
}
