<?php

use App\Models\Ride;
use App\States\AcceptedState;
use App\States\InProgressState;
use App\Exceptions\InvalidStateTransitionException;

// TC-21: Transição inválida completed → accepted (via AcceptedState após completed)
it('throws exception when trying to accept an already accepted ride', function () {
    $ride = Ride::factory()->make(['status' => 'accepted']);
    $state = new AcceptedState();

    expect(fn () => $state->accept($ride))
        ->toThrow(InvalidStateTransitionException::class);
});

it('transitions from accepted to in_progress when started', function () {
    $ride = Ride::factory()->make(['status' => 'accepted']);
    $state = new AcceptedState();

    $state->start($ride);

    expect($ride->status)->toBe('in_progress');
});

it('allows cancelling an accepted ride', function () {
    $ride = Ride::factory()->make(['status' => 'accepted']);
    $state = new AcceptedState();

    $state->cancel($ride);

    expect($ride->status)->toBe('cancelled');
});
