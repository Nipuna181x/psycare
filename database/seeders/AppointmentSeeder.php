<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DoctorClinicAffiliation;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a mix of upcoming, elevated-risk, and completed (with
     * prescriptions) appointments across active doctor+clinic pairings.
     */
    public function run(): void
    {
        $affiliations = DoctorClinicAffiliation::where('status', 'active')->get();
        $patients = User::where('email', 'like', '%@example.test')->get();

        if ($affiliations->isEmpty() || $patients->isEmpty()) {
            return;
        }

        // Upcoming confirmed appointments, spread across patients/doctors/clinics.
        foreach ($patients->take(8) as $index => $patient) {
            $affiliation = $affiliations[$index % $affiliations->count()];

            Appointment::factory()->for($affiliation->doctor)->create([
                'user_id' => $patient->id,
                'medical_center_id' => $affiliation->clinic_id,
                'patient_name' => $patient->name,
                'appointment_date' => now()->addDays(fake()->numberBetween(1, 10)),
                'status' => 'confirmed',
            ]);
        }

        // At least 2 elevated-risk appointments, for the Crisis Queue.
        foreach ($patients->slice(8, 2) as $index => $patient) {
            $affiliation = $affiliations[$index % $affiliations->count()];

            Appointment::factory()->for($affiliation->doctor)->create([
                'user_id' => $patient->id,
                'medical_center_id' => $affiliation->clinic_id,
                'patient_name' => $patient->name,
                'appointment_date' => now()->subDays(fake()->numberBetween(1, 5)),
                'status' => 'confirmed',
                'pre_assessment_risk_level' => 'elevated',
                'self_harm_flag' => true,
                'requires_immediate_escalation' => true,
                'phq9_total' => 22,
                'phq9_severity' => 'severe',
                'gad7_total' => 18,
                'gad7_severity' => 'severe',
                'screener_completed_at' => now()->subDays(1),
                'escalation_reviewed' => false,
            ]);
        }

        // At least 3 completed appointments with prescriptions, for Medication History.
        foreach ($patients->slice(10, 3) as $index => $patient) {
            $affiliation = $affiliations[$index % $affiliations->count()];

            $appointment = Appointment::factory()->for($affiliation->doctor)->create([
                'user_id' => $patient->id,
                'medical_center_id' => $affiliation->clinic_id,
                'patient_name' => $patient->name,
                'appointment_date' => now()->subDays(fake()->numberBetween(10, 30)),
                'status' => 'completed',
            ]);

            Prescription::factory()->create([
                'appointment_id' => $appointment->id,
                'patient_id' => $patient->id,
                'doctor_id' => $affiliation->doctor_id,
            ]);
        }
    }
}
