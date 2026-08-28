<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorAvailabilitySlot;
use App\Models\MedicalCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorAvailabilitySlot>
 */
class DoctorAvailabilitySlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->numberBetween(9, 16);

        return [
            'doctor_id' => Doctor::factory(),
            'clinic_id' => MedicalCenter::factory()->approved(),
            'date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $start),
            'end_time' => sprintf('%02d:30:00', $start),
            'is_booked' => false,
        ];
    }

    public function booked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_booked' => true,
        ]);
    }
}
