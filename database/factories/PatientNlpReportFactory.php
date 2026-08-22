<?php

namespace Database\Factories;

use App\Models\AiCompanionSession;
use App\Models\PatientNlpReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientNlpReport>
 */
class PatientNlpReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'appointment_id' => null,
            'ai_companion_session_id' => AiCompanionSession::factory(),
            'status' => 'generated',
            'schema_version' => '1.0',
            'report' => ['summary' => fake()->sentence()],
            'generated_at' => now(),
        ];
    }
}
