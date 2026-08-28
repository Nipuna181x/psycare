<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorClinicAffiliation>
 */
class DoctorClinicAffiliationFactory extends Factory
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
            'clinic_id' => MedicalCenter::factory()->approved(),
            'status' => 'active',
            'requested_by_clinic_at' => now()->subDays(fake()->numberBetween(5, 30)),
            'responded_by_doctor_at' => now()->subDays(fake()->numberBetween(1, 4)),
        ];
    }

    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'requested',
            'responded_by_doctor_at' => null,
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'declined',
            'responded_by_doctor_at' => now(),
        ]);
    }
}
