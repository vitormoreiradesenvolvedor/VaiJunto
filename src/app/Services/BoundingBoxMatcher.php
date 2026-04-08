<?php

namespace App\Services;

use App\Models\RideRequest;
use App\Models\User;
use App\Contracts\RideMatcherInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BoundingBoxMatcher implements RideMatcherInterface
{
    public function __construct(private float $tolerance = 0.05) {}

    public function findDrivers(RideRequest $request): Collection
    {
        [$lat, $lng] = explode(',', $request->origin_coords);

        return User::query()
            ->where('role', '!=', 'passenger')
            ->where('is_active', true)
            ->whereBetween('last_lat', [(float)$lat - $this->tolerance, (float)$lat + $this->tolerance])
            ->whereBetween('last_lng', [(float)$lng - $this->tolerance, (float)$lng + $this->tolerance])
            ->whereDoesntHave('rides', function ($q) use ($request) {
                $isSqlite = DB::connection()->getDriverName() === 'sqlite';
                $timeDiff = $isSqlite
                    ? 'ABS((julianday(started_at) - julianday(?)) * 1440) < 60'
                    : 'ABS(TIMESTAMPDIFF(MINUTE, started_at, ?)) < 60';

                $q->where('status', 'in_progress')
                  ->orWhere(function ($q2) use ($request, $timeDiff) {
                      $q2->where('status', 'accepted')
                         ->whereRaw($timeDiff, [$request->scheduled_for]);
                  });
            })
            ->get();
    }
}
