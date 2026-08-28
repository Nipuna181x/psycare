<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
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
            'doctor_id' => Doctor::factory(),
            'medical_center_id' => MedicalCenter::factory()->approved(),
            'appointment_date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'appointment_time' => fake()->randomElement(['09:00', '10:30', '13:00', '15:30', '16:30']),
            'mode' => fake()->randomElement(['in_person', 'online']),
            'patient_name' => fake()->name(),
            'patient_age' => fake()->numberBetween(16, 80),
            'patient_gender' => fake()->randomElement(['male', 'female', 'other']),
            'patient_phone' => fake()->numerify('07########'),
            'patient_email' => fake()->safeEmail(),
            'reason' => fake()->sentence(),
            'consultation_fee' => fake()->randomElement([3200, 3500, 4000, 4500]),
            'pre_assessment' => [
                ['question' => 'What brings you in today?', 'answer' => fake()->sentence()],
            ],
            'pre_assessment_mood_rating' => fake()->numberBetween(1, 10),
            'pre_assessment_summary' => fake()->paragraph(),
            'pre_assessment_risk_level' => fake()->randomElement(['low', 'moderate', 'elevated']),
            'status' => 'confirmed',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'cancelled']);
    }
}
