<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\MedicalCenter;
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
            'medical_center_id' => MedicalCenter::factory()->approved(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => fake()->unique()->userName(),
            'password' => static::$password ??= Hash::make('password'),
            'specialization' => fake()->randomElement(['Psychiatry', 'Psychology', 'Counseling', 'Neurology']),
            'phone' => fake()->phoneNumber(),
            'status' => 'active',
        ];
    }
}
