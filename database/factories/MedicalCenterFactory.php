<?php

namespace Database\Factories;

use App\Models\MedicalCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<MedicalCenter>
 */
class MedicalCenterFactory extends Factory
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
            'name' => fake()->company().' Medical Center',
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'registration_number' => fake()->unique()->bothify('REG-####??'),
            'password' => static::$password ??= Hash::make('password'),
            'status' => 'pending',
            'facility_fee' => fake()->randomElement([500, 750, 1000, 1500]),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
