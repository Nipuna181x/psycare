<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Amaya Silva', 'Kasun Weerasinghe', 'Nethmi Rathnayake', 'Tharindu Jayasekara',
            'Sanduni Mendis', 'Chamod Wijeratne', 'Dilki Ranasinghe', 'Isuru Bandaranayake',
            'Yasodha Kularatne', 'Chathura Amarasinghe', 'Piumi Gunawardena', 'Roshan de Silva',
            'Vindya Senanayake', 'Lakmal Herath',
            'Oshadi Karunathilaka', 'Buddhika Wanniarachchi', 'Malsha Abeywickrama',
        ];

        $password = Hash::make('password');

        foreach ($names as $name) {
            User::firstOrCreate(
                ['email' => Str::slug($name).'@example.test'],
                [
                    'name' => $name,
                    'mobile' => '07'.fake()->numerify('########'),
                    'email_verified_at' => now(),
                    'password' => $password,
                ]
            );
        }
    }
}
