<?php

namespace Database\Seeders;

use App\Models\MedicalCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clinics = [
            ['city' => 'Colombo', 'name' => 'Serene Mind Clinic', 'address' => 'Galle Road, Colombo 03, Colombo', 'facility_fee' => 1000],
            ['city' => 'Kandy', 'name' => 'Hill Country Medical Institute', 'address' => 'Kandy City Centre, Kandy', 'facility_fee' => 800],
            ['city' => 'Galle', 'name' => 'Southern Care Collective', 'address' => 'Galle Fort, Galle', 'facility_fee' => 900],
            ['city' => 'Kurunegala', 'name' => 'North Western Wellbeing Centre', 'address' => 'Kandy Road, Kurunegala', 'facility_fee' => 700],
            ['city' => 'Jaffna', 'name' => 'Northern Mind Practice', 'address' => 'Hospital Road, Jaffna', 'facility_fee' => 750],
        ];

        $password = Hash::make('password');

        foreach ($clinics as $clinic) {
            MedicalCenter::firstOrCreate(
                ['email' => Str::slug($clinic['city']).'.clinic@psycare.test'],
                [
                    'name' => $clinic['name'],
                    'phone' => '011'.fake()->numerify('#######'),
                    'address' => $clinic['address'],
                    'registration_number' => 'REG-'.Str::upper(Str::random(6)),
                    'password' => $password,
                    'status' => 'approved',
                    'facility_fee' => $clinic['facility_fee'],
                ]
            );
        }
    }
}
