<?php

use App\Models\User;
use App\Models\RideRequest;
use App\Services\BoundingBoxMatcher;

// TC-26: motorista dentro do raio é retornado
it('includes driver within bounding box tolerance', function () {
    $request = RideRequest::factory()->make([
        'origin_coords'   => '-21.2300,-45.0000',
        'scheduled_for'   => now()->addHour(),
    ]);

    $driver = User::factory()->driver()->make([
        'last_known_coords' => '-21.2320,-45.0020', // ~0.002° de distância
    ]);

    $matcher = new BoundingBoxMatcher(tolerance: 0.05);

    $results = $matcher->findDrivers($request);

    expect($results)->toContain($driver);
});

// TC-27: motorista fora do raio não é retornado
it('excludes driver outside bounding box tolerance', function () {
    $request = RideRequest::factory()->make([
        'origin_coords' => '-21.2300,-45.0000',
        'scheduled_for' => now()->addHour(),
    ]);

    $driver = User::factory()->driver()->make([
        'last_known_coords' => '-21.3500,-45.2000', // ~0.12° de distância
    ]);

    $matcher = new BoundingBoxMatcher(tolerance: 0.05);

    $results = $matcher->findDrivers($request);

    expect($results)->not->toContain($driver);
});

// TC-28: motorista ocupado no horário não é retornado
it('excludes driver unavailable at requested time', function () {
    $request = RideRequest::factory()->make([
        'origin_coords' => '-21.2300,-45.0000',
        'scheduled_for' => now()->addHour(),
    ]);

    $driver = User::factory()->driver()->unavailableAt(now()->addHour())->make([
        'last_known_coords' => '-21.2310,-45.0010',
    ]);

    $matcher = new BoundingBoxMatcher(tolerance: 0.05);

    $results = $matcher->findDrivers($request);

    expect($results)->not->toContain($driver);
});
