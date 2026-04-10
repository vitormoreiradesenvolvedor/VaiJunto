<?php

namespace App\Services;

use App\Contracts\RideMatcherInterface;
use App\Models\RideRequest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Matcher de desenvolvimento: retorna todos os usuários com papel de motorista,
 * sem filtro geográfico. Troque pelo BoundingBoxMatcher em produção.
 */
class AllDriversMatcher implements RideMatcherInterface
{
    public function findDrivers(RideRequest $request): Collection
    {
        return User::where('role', '!=', 'passenger')->get();
    }
}
