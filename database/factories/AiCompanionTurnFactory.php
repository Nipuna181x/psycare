<?php

namespace Database\Factories;

use App\Models\AiCompanionTurn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiCompanionTurn>
 */
class AiCompanionTurnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_companion_session_id' => AiCompanionSession::factory(),
            'role' => 'user',
            'sequence' => 1,
            'content' => fake()->sentence(),
        ];
    }
}
