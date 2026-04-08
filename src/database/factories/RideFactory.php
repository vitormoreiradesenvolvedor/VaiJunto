<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'driver_id'    => User::factory()->driver(),
            'passenger_id' => User::factory()->passenger(),
            'type'         => 'demand',
            'status'       => 'pending',
        ];
    }
}
