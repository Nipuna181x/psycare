<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
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
            'doctor_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])->doctor_id,
            'patient_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])->user_id,
            'clinic_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])->medical_center_id,
            'notes' => fake()->optional()->sentence(),
            'issued_at' => now(),
        ];
    }

    /**
     * Attach a default set of items after creation, when none were explicitly created.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Prescription $prescription): void {
            if ($prescription->items()->doesntExist()) {
                PrescriptionItem::factory()->count(fake()->numberBetween(1, 3))->for($prescription)->create();
            }
        });
    }
}
