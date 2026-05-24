<?php

use App\Models\User;
use App\Models\Vehicle;
use App\Models\RideRequest;
use App\Models\Ride;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-14: passageiro solicita carona com dados válidos
it('passenger can request a ride with valid data', function () {
    $passenger = User::factory()->passenger()->create();

    $response = $this->actingAs($passenger)->postJson('/rides/request', [
        'origin'       => 'Rua das Flores, Lavras',
        'destination'  => 'UFLA - Campus Universitário',
        'origin_coords'    => '-21.2300,-45.0000',
        'destination_coords' => '-21.2410,-45.0010',
        'scheduled_for'    => now()->addHour()->toISOString(),
        'seats_needed' => 1,
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('ride_requests', [
        'passenger_id' => $passenger->id,
        'status'       => 'pending',
    ]);
});

// TC-15: motorista aceita solicitação
it('driver can accept a ride request', function () {
    $driver  = User::factory()->driver()->create();
    $vehicle = Vehicle::factory()->create(['user_id' => $driver->id]);
    $request = RideRequest::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($driver)->postJson("/rides/{$request->id}/accept");

    $response->assertStatus(200);
    $this->assertDatabaseHas('rides', [
        'driver_id' => $driver->id,
        'status'    => 'accepted',
    ]);
    $this->assertDatabaseHas('ride_requests', [
        'id'     => $request->id,
        'status' => 'accepted',
    ]);
});

// TC-16: motorista recusa solicitação
it('driver can reject a ride request', function () {
    $driver  = User::factory()->driver()->create();
    $request = RideRequest::factory()->create(['status' => 'pending']);

    $response = $this->actingAs($driver)->postJson("/rides/{$request->id}/reject");

    $response->assertStatus(200);
    $this->assertDatabaseHas('ride_requests', [
        'id'     => $request->id,
        'status' => 'rejected',
    ]);
});

// TC-17: passageiro cancela carona aceita com justificativa
it('passenger can cancel an accepted ride with a reason', function () {
    $passenger = User::factory()->passenger()->create();
    $ride = Ride::factory()->create([
        'status'       => 'accepted',
        'passenger_id' => $passenger->id,
    ]);

    $response = $this->actingAs($passenger)->postJson("/rides/{$ride->id}/cancel", [
        'reason' => 'Mudança de planos',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('rides', [
        'id'            => $ride->id,
        'status'        => 'cancelled',
        'cancel_reason' => 'Mudança de planos',
    ]);
});

// TC-18: endpoint sem autenticação retorna 401
it('returns 401 for unauthenticated ride request', function () {
    $response = $this->postJson('/rides/request', []);

    $response->assertStatus(401);
});
