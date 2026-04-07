<?php

namespace App\Services;

use App\Models\User;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;

class PointService
{
    public function award(int $userId, int $amount, string $reason): void
    {
        DB::transaction(function () use ($userId, $amount, $reason) {
            PointTransaction::create([
                'user_id' => $userId,
                'amount'  => $amount,
                'reason'  => $reason,
            ]);

            User::where('id', $userId)->increment('points_balance', $amount);
        });
    }
}
