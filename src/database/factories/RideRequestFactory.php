<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RideRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'passenger_id'        => User::factory()->passenger(),
            'origin'              => fake()->address(),
            'destination'         => 'UFLA - Campus Universitário',
            'origin_coords'       => '-21.2300,-45.0000',
            'destination_coords'  => '-21.2410,-45.0010',
            'scheduled_for'       => now()->addHour(),
            'seats_needed'        => 1,
            'status'              => 'pending',
        ];
    }
}
