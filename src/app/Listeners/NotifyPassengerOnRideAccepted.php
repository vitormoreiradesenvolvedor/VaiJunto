<?php

namespace App\Listeners;

use App\Events\RideAccepted;
use App\Services\NotificationService;

class NotifyPassengerOnRideAccepted
{
    public function __construct(private NotificationService $notificationService) {}

    public function handle(RideAccepted $event): void
    {
        $ride = $event->ride;

        $this->notificationService->notify(
            user:    $ride->passenger,
            type:    'ride_accepted',
            payload: [
                'ride_id'   => $ride->id,
                'driver_id' => $ride->driver_id,
            ]
        );
    }
}
