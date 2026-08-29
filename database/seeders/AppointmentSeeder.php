<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\DoctorClinicAffiliation;
use App\Models\PatientConsent;
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
                'clinic_id' => $affiliation->clinic_id,
            ]);
        }

        // Fixture data for the consent-gated cross-doctor history feature, covering
        // all 4 required manual-test scenarios. Uses patient indices 13-16, which are
        // untouched by the scenarios above, and two affiliations from different
        // clinics so the "other provider" history genuinely crosses clinics.
        if ($affiliations->count() >= 2 && $patients->count() >= 17) {
            $doctorAAffiliation = $affiliations[0];
            $doctorBAffiliation = $affiliations->first(fn (DoctorClinicAffiliation $affiliation) => $affiliation->clinic_id !== $doctorAAffiliation->clinic_id) ?? $affiliations[1];

            // Scenario 1: single-doctor patient — no "other providers" section should render.
            $singleDoctorPatient = $patients[13];
            Appointment::factory()->for($doctorAAffiliation->doctor)->create([
                'user_id' => $singleDoctorPatient->id,
                'medical_center_id' => $doctorAAffiliation->clinic_id,
                'patient_name' => $singleDoctorPatient->name,
                'status' => 'completed',
            ]);

            // Scenario 2: multi-doctor patient, no consent granted — locked section.
            $noConsentPatient = $patients[14];
            Appointment::factory()->for($doctorAAffiliation->doctor)->create([
                'user_id' => $noConsentPatient->id,
                'medical_center_id' => $doctorAAffiliation->clinic_id,
                'patient_name' => $noConsentPatient->name,
                'status' => 'completed',
            ]);
            Appointment::factory()->for($doctorBAffiliation->doctor)->create([
                'user_id' => $noConsentPatient->id,
                'medical_center_id' => $doctorBAffiliation->clinic_id,
                'patient_name' => $noConsentPatient->name,
                'status' => 'completed',
            ]);

            // Scenario 3: multi-doctor patient, active consent granted to doctor A.
            $consentedPatient = $patients[15];
            Appointment::factory()->for($doctorAAffiliation->doctor)->create([
                'user_id' => $consentedPatient->id,
                'medical_center_id' => $doctorAAffiliation->clinic_id,
                'patient_name' => $consentedPatient->name,
                'status' => 'completed',
            ]);
            Appointment::factory()->for($doctorBAffiliation->doctor)->create([
                'user_id' => $consentedPatient->id,
                'medical_center_id' => $doctorBAffiliation->clinic_id,
                'patient_name' => $consentedPatient->name,
                'status' => 'completed',
            ]);
            PatientConsent::factory()->create([
                'patient_id' => $consentedPatient->id,
                'doctor_id' => $doctorAAffiliation->doctor_id,
            ]);

            // Scenario 4: currently elevated-risk patient, no consent granted — the
            // emergency override should unlock the section regardless.
            $elevatedRiskPatient = $patients[16];
            Appointment::factory()->for($doctorAAffiliation->doctor)->create([
                'user_id' => $elevatedRiskPatient->id,
                'medical_center_id' => $doctorAAffiliation->clinic_id,
                'patient_name' => $elevatedRiskPatient->name,
                'status' => 'completed',
            ]);
            Appointment::factory()->for($doctorBAffiliation->doctor)->create([
                'user_id' => $elevatedRiskPatient->id,
                'medical_center_id' => $doctorBAffiliation->clinic_id,
                'patient_name' => $elevatedRiskPatient->name,
                'status' => 'completed',
                'self_harm_flag' => true,
                'requires_immediate_escalation' => true,
                'screener_completed_at' => now()->subHours(2),
            ]);
        }
    }
}
