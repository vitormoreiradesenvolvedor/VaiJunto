<?php

use App\Models\Ride;
use App\States\PendingState;
use App\States\AcceptedState;
use App\Exceptions\InvalidStateTransitionException;

// TC-19: Transição válida pending → accepted
it('transitions from pending to accepted when accepted', function () {
    $ride = Ride::factory()->make(['status' => 'pending']);
    $state = new PendingState();

    $state->accept($ride);

    expect($ride->status)->toBe('accepted');
});

// TC-20: Transição inválida pending → completed
it('throws exception when trying to complete a pending ride', function () {
    $ride = Ride::factory()->make(['status' => 'pending']);
    $state = new PendingState();

    expect(fn () => $state->complete($ride))
        ->toThrow(InvalidStateTransitionException::class);
});

// TC-20 variante: pending → in_progress
it('throws exception when trying to start a pending ride', function () {
    $ride = Ride::factory()->make(['status' => 'pending']);
    $state = new PendingState();

    expect(fn () => $state->start($ride))
        ->toThrow(InvalidStateTransitionException::class);
});

// TC-19 variante: cancelling a pending ride is valid
it('allows cancelling a pending ride', function () {
    $ride = Ride::factory()->make(['status' => 'pending']);
    $state = new PendingState();

    $state->cancel($ride);

    expect($ride->status)->toBe('cancelled');
});
