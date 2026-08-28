<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Database\Seeder;

class AffiliationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Distributes affiliations so the doctor-clinic switcher and view-only
     * booking states are both exercised: the first 2 doctors get zero
     * affiliations, the next 7 get exactly one, and the remaining 6 get 2-3.
     */
    public function run(): void
    {
        $doctors = Doctor::orderBy('id')->get();
        $clinics = MedicalCenter::all();

        foreach ($doctors as $index => $doctor) {
            $clinicCount = match (true) {
                $index < 2 => 0,
                $index < 9 => 1,
                default => fake()->numberBetween(2, 3),
            };

            if ($clinicCount === 0) {
                continue;
            }

            $assignedClinics = $clinics->random(min($clinicCount, $clinics->count()));

            foreach ($assignedClinics as $clinic) {
                DoctorClinicAffiliation::firstOrCreate(
                    ['doctor_id' => $doctor->id, 'clinic_id' => $clinic->id],
                    [
                        'status' => 'active',
                        'requested_by_clinic_at' => now()->subDays(fake()->numberBetween(5, 30)),
                        'responded_by_doctor_at' => now()->subDays(fake()->numberBetween(1, 4)),
                    ]
                );
            }
        }
    }
}
