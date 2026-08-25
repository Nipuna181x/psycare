<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\TherapyRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TherapyRoom>
 */
class TherapyRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'title' => fake()->randomElement(['Anxiety Support Circle', 'Grief & Loss Group', 'Stress Management Session', 'Peer Support Meetup']),
            'topic' => fake()->sentence(),
            'status' => 'scheduled',
            'scheduled_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
        ];
    }

    public function live(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'live',
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'cancelled']);
    }
}
