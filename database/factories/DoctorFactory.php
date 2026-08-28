<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'license_number' => fake()->unique()->bothify('SLMC-####'),
            'specialization' => fake()->randomElement(['Psychiatry', 'Psychology', 'Counseling', 'Neurology']),
            'years_of_experience' => fake()->numberBetween(2, 20),
            'consultation_fee' => fake()->numberBetween(2500, 6000),
            'consultation_mode' => fake()->randomElement(['in_person', 'online', 'both']),
            'rating' => fake()->randomFloat(1, 4.0, 5.0),
            'phone' => fake()->phoneNumber(),
            'status' => 'approved',
            'onboarding_step' => 'profile_complete',
            'approved_at' => now(),
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_approval',
            'onboarding_step' => 'basic_info_done',
            'approved_at' => null,
        ]);
    }
}
