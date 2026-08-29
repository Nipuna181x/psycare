<?php

namespace Tests\Feature\Patient;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('health-records.index'))->assertRedirect(route('login'));
    }

    public function test_patient_can_view_their_own_health_records(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['name' => 'Dr. Records']);
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id]);

        $this->actingAs($patient)
            ->get(route('health-records.index'))
            ->assertOk()
            ->assertSee('My health records')
            ->assertSee('Dr. Records');
    }

    public function test_patient_sees_no_medications_empty_state_with_no_prescriptions(): void
    {
        $patient = User::factory()->create();
        Appointment::factory()->create(['user_id' => $patient->id]);

        $this->actingAs($patient)
            ->get(route('health-records.index'))
            ->assertOk()
            ->assertSee('No medications recorded yet');
    }

    public function test_patient_sees_their_own_prescription_medicines(): void
    {
        $patient = User::factory()->create();
        $appointment = Appointment::factory()->create(['user_id' => $patient->id]);
        $prescription = Prescription::factory()->create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $patient->id,
        ]);

        $medicineName = $prescription->items->first()->medicine_name;

        $this->actingAs($patient)
            ->get(route('health-records.index'))
            ->assertOk()
            ->assertSee($medicineName);
    }

    public function test_patient_only_sees_their_own_records_not_another_patients(): void
    {
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create();
        Appointment::factory()->create(['user_id' => $otherPatient->id, 'patient_name' => 'Someone Else']);

        $this->actingAs($patient)
            ->get(route('health-records.index'))
            ->assertOk()
            ->assertDontSee('Someone Else');
    }
}
