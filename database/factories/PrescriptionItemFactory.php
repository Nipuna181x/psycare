<?php

namespace Database\Factories;

use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrescriptionItem>
 */
class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medicine_name' => fake()->randomElement(['Sertraline', 'Escitalopram', 'Fluoxetine', 'Mirtazapine', 'Lorazepam']),
            'dosage' => fake()->randomElement(['10 mg', '25 mg', '50 mg']),
            'frequency' => fake()->randomElement(['Once daily', 'Twice daily', 'At night']),
            'duration' => fake()->randomElement(['2 weeks', '30 days', '3 months', null]),
            'special_instructions' => fake()->optional()->sentence(),
        ];
    }
}
