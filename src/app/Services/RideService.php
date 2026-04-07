<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\User;
use App\Events\RideCompleted;
use App\Events\RideAccepted;
use App\Factories\RideFactory;
use App\States\PendingState;
use App\States\AcceptedState;
use App\States\InProgressState;
use App\Contracts\RideMatcherInterface;

class RideService
{
    public function __construct(
        private RideMatcherInterface $matcher,
        private RideFactory $factory,
        private NotificationService $notificationService,
    ) {}

    public function request(array $data, User $passenger): RideRequest
    {
        $rideRequest = RideRequest::create([
            ...$data,
            'passenger_id' => $passenger->id,
            'status'       => 'pending',
        ]);

        $drivers = $this->matcher->findDrivers($rideRequest);

        foreach ($drivers as $driver) {
            $this->notificationService->notify($driver, 'new_ride_request', [
                'ride_request_id' => $rideRequest->id,
            ]);
        }

        return $rideRequest;
    }

    public function accept(RideRequest $rideRequest, User $driver): Ride
    {
        $ride = $this->factory->createFromDemandRequest($rideRequest, $driver);

        $rideRequest->update(['status' => 'accepted']);

        (new PendingState())->accept($ride);

        return $ride;
    }

    public function start(Ride $ride): void
    {
        (new AcceptedState())->start($ride);
    }

    public function complete(Ride $ride): void
    {
        (new InProgressState())->complete($ride);
    }

    public function cancel(Ride $ride, string $reason): void
    {
        $state = match ($ride->status) {
            'pending'     => new PendingState(),
            'accepted'    => new AcceptedState(),
            'in_progress' => new InProgressState(),
            default       => throw new \LogicException("Carona com status '{$ride->status}' não pode ser cancelada."),
        };

        $ride->cancel_reason = $reason;
        $state->cancel($ride);
    }
}
