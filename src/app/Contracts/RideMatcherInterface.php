<?php

namespace App\Contracts;

use App\Models\RideRequest;
use Illuminate\Support\Collection;

interface RideMatcherInterface
{
    public function findDrivers(RideRequest $request): Collection;
}
