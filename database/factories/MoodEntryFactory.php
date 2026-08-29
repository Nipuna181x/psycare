<?php

namespace Database\Factories;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MoodEntry>
 */
class MoodEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'mood_score' => fake()->numberBetween(1, 5),
            'mood_tags' => fake()->randomElements(['anxious', 'calm', 'stressed', 'sad', 'happy', 'tired', 'energetic', 'hopeful'], fake()->numberBetween(0, 3)),
            'sleep_hours' => fake()->optional()->randomFloat(1, 0, 12),
            'note' => fake()->optional()->sentence(),
            'entry_date' => fake()->unique()->dateTimeBetween('-30 days', 'today')->format('Y-m-d'),
        ];
    }
}
