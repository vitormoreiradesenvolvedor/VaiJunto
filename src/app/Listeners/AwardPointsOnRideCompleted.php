<?php

namespace App\Listeners;

use App\Events\RideCompleted;
use App\Services\PointService;

class AwardPointsOnRideCompleted
{
    public function __construct(private PointService $pointService) {}

    public function handle(RideCompleted $event): void
    {
        $this->pointService->award(
            userId: $event->ride->driver_id,
            amount: 10,
            reason: 'carona concluída'
        );
    }
}
