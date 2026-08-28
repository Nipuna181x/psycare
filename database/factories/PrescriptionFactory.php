<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'patient_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])->user_id,
            'doctor_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])->doctor_id,
            'medication_name' => fake()->randomElement(['Sertraline', 'Escitalopram', 'Fluoxetine']),
            'dosage' => fake()->randomElement(['10 mg', '25 mg', '50 mg']),
            'frequency' => fake()->randomElement(['Once daily', 'Twice daily', 'At night']),
            'notes' => fake()->sentence(),
        ];
    }
}
