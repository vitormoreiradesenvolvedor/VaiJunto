<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'           => fake()->name(),
            'email'          => fake()->unique()->safeEmail(),
            'avatar'         => null,
            'role'           => 'passenger',
            'is_active'      => true,
            'points_balance' => 0,
            'last_lat'       => null,
            'last_lng'       => null,
        ];
    }

    public function driver(): static
    {
        return $this->state([
            'role'      => 'driver',
            'is_active' => true,
            'last_lat'  => -21.2300,
            'last_lng'  => -45.0000,
        ]);
    }

    public function passenger(): static
    {
        return $this->state([
            'role' => 'passenger',
        ]);
    }

    public function unavailableAt(\DateTimeInterface $dateTime): static
    {
        return $this->state([
            'available_until' => $dateTime,
        ]);
    }
}
