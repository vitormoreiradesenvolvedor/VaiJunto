<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FixedRouteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'driver_id'   => User::factory()->driver(),
            'origin'      => fake()->address(),
            'destination' => 'UFLA - Campus Universitário',
            'departure_time' => '07:30',
            'days_of_week'   => ['monday', 'wednesday', 'friday'],
            'available_seats' => 3,
            'status'         => 'active',
        ];
    }
}
