<?php

use App\Models\User;
use App\Models\Ride;
use App\Models\Vehicle;
use App\Models\FixedRoute;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-35: passageiro não pode aceitar carona
it('returns 403 when passenger tries to accept a ride', function () {
    $passenger   = User::factory()->passenger()->create();
    $rideRequest = \App\Models\RideRequest::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($passenger)
        ->postJson("/rides/{$rideRequest->id}/accept");

    $response->assertStatus(403);
});

// TC-36: usuário não pode editar veículo de outro usuário
it('returns 403 when user tries to edit another user vehicle', function () {
    $owner   = User::factory()->driver()->create();
    $other   = User::factory()->driver()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($other)
        ->putJson("/vehicles/{$vehicle->id}", ['model' => 'Novo Modelo']);

    $response->assertStatus(403);
});

// TC-37: rota protegida redireciona não autenticado
it('redirects unauthenticated user from protected route', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

// TC-13: passageiro não pode pausar rota de outro usuário
it('returns 403 when passenger tries to pause another driver route', function () {
    $driver    = User::factory()->driver()->create();
    $passenger = User::factory()->passenger()->create();
    $route     = FixedRoute::factory()->create(['driver_id' => $driver->id]);

    $response = $this->actingAs($passenger)
        ->postJson("/routes/{$route->id}/pause");

    $response->assertStatus(403);
});
