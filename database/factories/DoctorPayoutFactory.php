<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\MedicalCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorPayout>
 */
class DoctorPayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clinic_id' => MedicalCenter::factory()->approved(),
            'doctor_id' => Doctor::factory(),
            'marked_by_type' => 'medical_center',
            'marked_by_id' => fn (array $attributes) => $attributes['clinic_id'],
            'marked_by_name' => fake()->company(),
            'amount' => fake()->randomFloat(2, 1000, 25000),
            'payment_count' => fake()->numberBetween(1, 8),
            'paid_at' => now(),
            'status' => 'paid',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'received_at' => now(),
        ]);
    }
}
