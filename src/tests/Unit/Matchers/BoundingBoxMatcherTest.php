<?php

use App\Models\User;
use App\Models\RideRequest;
use App\Services\BoundingBoxMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-26: motorista dentro do raio é retornado
it('includes driver within bounding box tolerance', function () {
    $request = RideRequest::factory()->create([
        'origin_coords' => '-21.2300,-45.0000',
        'scheduled_for' => now()->addHour(),
    ]);

    User::factory()->driver()->create([
        'last_lat' => -21.2320,
        'last_lng' => -45.0020,
    ]);

    $matcher = new BoundingBoxMatcher(tolerance: 0.05);
    $results = $matcher->findDrivers($request);

    expect($results)->toHaveCount(1);
});

// TC-27: motorista fora do raio não é retornado
it('excludes driver outside bounding box tolerance', function () {
    $request = RideRequest::factory()->create([
        'origin_coords' => '-21.2300,-45.0000',
        'scheduled_for' => now()->addHour(),
    ]);

    User::factory()->driver()->create([
        'last_lat' => -21.3500,
        'last_lng' => -45.2000,
    ]);

    $matcher = new BoundingBoxMatcher(tolerance: 0.05);
    $results = $matcher->findDrivers($request);

    expect($results)->toHaveCount(0);
});

// TC-28: motorista ocupado no horário não é retornado
it('excludes driver unavailable at requested time', function () {
    $request = RideRequest::factory()->create([
        'origin_coords' => '-21.2300,-45.0000',
        'scheduled_for' => now()->addHour(),
    ]);

    $driver = User::factory()->driver()->create([
        'last_lat' => -21.2310,
        'last_lng' => -45.0010,
    ]);

    // Motorista com carona em andamento — deve ser excluído
    $driver->rides()->create([
        'status'     => 'in_progress',
        'type'       => 'demand',
        'started_at' => now(),
    ]);

    $matcher = new BoundingBoxMatcher(tolerance: 0.05);
    $results = $matcher->findDrivers($request);

    expect($results)->toHaveCount(0);
});
