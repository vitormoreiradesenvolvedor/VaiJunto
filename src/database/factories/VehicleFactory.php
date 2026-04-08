<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->driver(),
            'model'   => fake()->randomElement(['Gol', 'Uno', 'HB20', 'Onix', 'Argo']),
            'plate'   => strtoupper(fake()->bothify('???-####')),
            'color'   => fake()->safeColorName(),
            'year'    => fake()->numberBetween(2010, 2024),
            'seats'   => 4,
        ];
    }
}
