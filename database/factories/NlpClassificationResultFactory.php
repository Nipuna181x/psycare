<?php

namespace Database\Factories;

use App\Models\NlpClassificationResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NlpClassificationResult>
 */
class NlpClassificationResultFactory extends Factory
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
            'entry_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'risk_level' => fake()->randomElement(['low', 'moderate', 'high', 'urgent']),
            'self_harm_flag' => false,
            'self_harm_confidence' => fake()->randomFloat(3, 0, 1),
            'phq9_severity' => fake()->randomElement(['minimal', 'mild', 'moderate', 'moderately_severe', 'severe']),
            'gad7_severity' => fake()->randomElement(['minimal', 'mild', 'moderate', 'severe']),
            'symptoms' => fake()->randomElements(
                ['insomnia', 'anhedonia', 'anxiety', 'fatigue', 'irritability', 'appetite_change'],
                fake()->numberBetween(1, 3)
            ),
            'symptom_scores' => [],
        ];
    }

    /**
     * Flag this entry as containing a self-harm signal.
     */
    public function selfHarmFlagged(): static
    {
        return $this->state(fn (array $attributes): array => [
            'self_harm_flag' => true,
            'self_harm_confidence' => fake()->randomFloat(3, 0.6, 1),
        ]);
    }
}
