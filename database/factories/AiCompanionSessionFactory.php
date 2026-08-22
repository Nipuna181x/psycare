<?php

namespace Database\Factories;

use App\Models\AiCompanionSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiCompanionSession>
 */
class AiCompanionSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => fake()->uuid(),
            'user_id' => User::factory(),
            'language' => 'en',
            'consented_at' => now(),
            'ended_at' => null,
        ];
    }
}
