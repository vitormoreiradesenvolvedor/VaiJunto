<?php

use App\Models\User;
use App\Services\PointService;
use App\Models\PointTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// TC-34: PointService::award() cria transação e atualiza saldo
it('creates a point transaction and increments user balance', function () {
    $user = User::factory()->create(['points_balance' => 0]);
    $service = new PointService();

    $service->award($user->id, 10, 'carona concluída');

    expect(PointTransaction::where('user_id', $user->id)->count())->toBe(1);
    expect($user->fresh()->points_balance)->toBe(10);
});

// TC-33: saldo reflete múltiplas transações
it('reflects cumulative balance across multiple transactions', function () {
    $user = User::factory()->create(['points_balance' => 0]);
    $service = new PointService();

    $service->award($user->id, 10, 'carona 1');
    $service->award($user->id, 10, 'carona 2');
    $service->award($user->id, 10, 'carona 3');

    expect($user->fresh()->points_balance)->toBe(30);
    expect(PointTransaction::where('user_id', $user->id)->count())->toBe(3);
});

it('stores the correct reason in the transaction', function () {
    $user = User::factory()->create(['points_balance' => 0]);
    $service = new PointService();

    $service->award($user->id, 10, 'carona concluída');

    $transaction = PointTransaction::where('user_id', $user->id)->first();
    expect($transaction->reason)->toBe('carona concluída');
    expect($transaction->amount)->toBe(10);
});
