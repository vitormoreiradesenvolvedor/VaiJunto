<?php

namespace App\Listeners;

use App\Events\RideCompleted;
use App\Services\NotificationService;

class NotifyPassengerOnRideCompleted
{
    public function __construct(private NotificationService $notificationService) {}

    public function handle(RideCompleted $event): void
    {
        $ride = $event->ride;

        $this->notificationService->notify(
            user:    $ride->passenger,
            type:    'ride_completed',
            payload: ['ride_id' => $ride->id]
        );

        $this->notificationService->notify(
            user:    $ride->driver,
            type:    'rate_passenger',
            payload: ['ride_id' => $ride->id, 'passenger_id' => $ride->passenger_id]
        );
    }
}
