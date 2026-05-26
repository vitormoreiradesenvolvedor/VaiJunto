<?php

namespace App\Factories;

use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\FixedRoute;
use App\Models\User;

class RideFactory
{
    public function createFromDemandRequest(RideRequest $request, User $driver): Ride
    {
        return Ride::create([
            'driver_id'       => $driver->id,
            'passenger_id'    => $request->passenger_id,
            'vehicle_id'      => $driver->vehicle->id,
            'ride_request_id' => $request->id,
            'type'            => 'demand',
            'status'          => 'pending',
        ]);
    }

    public function createFromFixedRoute(FixedRoute $route, User $driver): Ride
    {
        return Ride::create([
            'driver_id'      => $driver->id,
            'vehicle_id'     => $route->vehicle_id,
            'fixed_route_id' => $route->id,
            'type'           => 'fixed',
            'status'         => 'pending',
        ]);
    }
}
