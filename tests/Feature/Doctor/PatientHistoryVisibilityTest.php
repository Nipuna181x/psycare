<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PatientConsent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientHistoryVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_doctor_patient_shows_no_other_providers_section(): void
    {
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id]);

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertDontSee('Care History with Other Providers');
    }

    public function test_multi_doctor_patient_with_no_consent_shows_locked_message(): void
    {
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctorA)->create(['user_id' => $patient->id]);
        Appointment::factory()->for($doctorB)->create(['user_id' => $patient->id]);

        $this->actingAs($doctorA, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee('Care History with Other Providers')
            ->assertSee('has not granted you access');
    }

    public function test_multi_doctor_patient_with_active_consent_shows_other_providers_history(): void
    {
        $doctorA = Doctor::factory()->create(['name' => 'Alicia Consented']);
        $doctorB = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctorA)->create(['user_id' => $patient->id]);
        Appointment::factory()->for($doctorB)->create(['user_id' => $patient->id]);
        PatientConsent::factory()->create(['patient_id' => $patient->id, 'doctor_id' => $doctorA->id]);

        $response = $this->actingAs($doctorA, 'doctor')->get(route('doctor.patients.show', $patient));

        $response->assertOk()
            ->assertSee('Care History with Other Providers')
            ->assertSee($doctorB->name)
            ->assertDontSee('has not granted you access');
    }

    public function test_revoked_consent_behaves_as_locked(): void
    {
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctorA)->create(['user_id' => $patient->id]);
        Appointment::factory()->for($doctorB)->create(['user_id' => $patient->id]);
        PatientConsent::factory()->revoked()->create(['patient_id' => $patient->id, 'doctor_id' => $doctorA->id]);

        $this->actingAs($doctorA, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertSee('has not granted you access');
    }

    public function test_elevated_risk_patient_unlocks_emergency_override_without_consent(): void
    {
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create(['name' => 'Bailey Emergency']);
        $patient = User::factory()->create();
        Appointment::factory()->for($doctorA)->create(['user_id' => $patient->id]);
        Appointment::factory()->for($doctorB)->create([
            'user_id' => $patient->id,
            'self_harm_flag' => true,
            'requires_immediate_escalation' => true,
            'screener_completed_at' => now(),
        ]);

        $response = $this->actingAs($doctorA, 'doctor')->get(route('doctor.patients.show', $patient));

        $response->assertOk()
            ->assertSee('Emergency / crisis override active')
            ->assertSee($doctorB->name);
    }

    public function test_stale_elevated_risk_appointment_does_not_trigger_override_when_a_newer_normal_appointment_exists(): void
    {
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctorA)->create(['user_id' => $patient->id]);
        Appointment::factory()->for($doctorB)->create([
            'user_id' => $patient->id,
            'self_harm_flag' => true,
            'requires_immediate_escalation' => true,
            'screener_completed_at' => now()->subDays(10),
        ]);
        Appointment::factory()->for($doctorB)->create([
            'user_id' => $patient->id,
            'self_harm_flag' => false,
            'requires_immediate_escalation' => false,
            'screener_completed_at' => now(),
        ]);

        $this->actingAs($doctorA, 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertOk()
            ->assertDontSee('Emergency / crisis override active')
            ->assertSee('has not granted you access');
    }
}
