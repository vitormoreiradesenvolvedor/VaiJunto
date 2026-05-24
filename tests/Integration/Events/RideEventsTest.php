<?php

use App\Models\User;
use App\Models\Ride;
use App\Events\RideCompleted;
use App\Events\RideAccepted;
use App\Models\PointTransaction;
use App\Models\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-23: RideCompleted dispara AwardPointsOnRideCompleted
it('awards points to driver when ride is completed', function () {
    $driver    = User::factory()->driver()->create(['points_balance' => 0]);
    $passenger = User::factory()->passenger()->create();
    $ride      = Ride::factory()->create([
        'driver_id'    => $driver->id,
        'passenger_id' => $passenger->id,
        'status'       => 'completed',
    ]);

    event(new RideCompleted($ride));

    expect(PointTransaction::where('user_id', $driver->id)->count())->toBe(1);
    expect($driver->fresh()->points_balance)->toBe(10);
});

// TC-24: RideCompleted cria notificação para o passageiro
it('notifies passenger when ride is completed', function () {
    $driver    = User::factory()->driver()->create();
    $passenger = User::factory()->passenger()->create();
    $ride      = Ride::factory()->create([
        'driver_id'    => $driver->id,
        'passenger_id' => $passenger->id,
        'status'       => 'completed',
    ]);

    event(new RideCompleted($ride));

    $this->assertDatabaseHas('notifications', [
        'user_id' => $passenger->id,
        'type'    => 'ride_completed',
    ]);
});

// TC-24 variante: RideCompleted também notifica motorista para avaliar passageiro
it('notifies driver to rate passenger when ride is completed', function () {
    $driver    = User::factory()->driver()->create();
    $passenger = User::factory()->passenger()->create();
    $ride      = Ride::factory()->create([
        'driver_id'    => $driver->id,
        'passenger_id' => $passenger->id,
        'status'       => 'completed',
    ]);

    event(new RideCompleted($ride));

    $this->assertDatabaseHas('notifications', [
        'user_id' => $driver->id,
        'type'    => 'rate_passenger',
    ]);
});

// TC-25: RideAccepted cria notificação para o passageiro
it('notifies passenger when ride is accepted', function () {
    $driver    = User::factory()->driver()->create();
    $passenger = User::factory()->passenger()->create();
    $ride      = Ride::factory()->create([
        'driver_id'    => $driver->id,
        'passenger_id' => $passenger->id,
        'status'       => 'accepted',
    ]);

    event(new RideAccepted($ride));

    $this->assertDatabaseHas('notifications', [
        'user_id' => $passenger->id,
        'type'    => 'ride_accepted',
    ]);
});

// Garante que eventos são disparados em sequência correta
it('dispatches RideCompleted event when ride service completes a ride', function () {
    Event::fake([RideCompleted::class]);

    $driver    = User::factory()->driver()->create();
    $passenger = User::factory()->passenger()->create();
    $ride      = Ride::factory()->create([
        'driver_id'    => $driver->id,
        'passenger_id' => $passenger->id,
        'status'       => 'in_progress',
    ]);

    $service = app(\App\Services\RideService::class);
    $service->complete($ride);

    Event::assertDispatched(RideCompleted::class, function ($event) use ($ride) {
        return $event->ride->id === $ride->id;
    });
});
