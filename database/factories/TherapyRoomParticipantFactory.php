<?php

namespace Database\Factories;

use App\Models\TherapyRoom;
use App\Models\TherapyRoomParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TherapyRoomParticipant>
 */
class TherapyRoomParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'therapy_room_id' => TherapyRoom::factory(),
            'patient_id' => User::factory(),
            'anonymous_label' => 'Patient '.fake()->randomLetter(),
            'join_order' => fake()->unique()->numberBetween(1, 1000),
        ];
    }

    public function removed(): static
    {
        return $this->state(fn (array $attributes): array => ['removed_at' => now()]);
    }
}
