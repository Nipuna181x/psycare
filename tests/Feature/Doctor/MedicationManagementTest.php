<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_profile_shows_empty_medication_state(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.patients.show', $appointment->user))
            ->assertOk()
            ->assertSee('No medications recorded yet');
    }

    public function test_doctor_can_add_and_view_medication_grouped_by_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->post(route('doctor.appointments.medications.store', $appointment), [
                'medication_name' => 'Sertraline',
                'dosage' => '50 mg',
                'frequency' => 'Once daily',
                'notes' => 'For 30 days',
            ])->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->user_id,
            'doctor_id' => $appointment->doctor_id,
            'medication_name' => 'Sertraline',
        ]);

        $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.patients.show', $appointment->user))
            ->assertOk()
            ->assertSee('Medication History')
            ->assertSee('Sertraline')
            ->assertSee('Once daily');
    }

    public function test_doctor_cannot_add_medication_to_another_doctors_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs(Doctor::factory()->create(), 'doctor')
            ->post(route('doctor.appointments.medications.store', $appointment), [
                'medication_name' => 'Medication', 'dosage' => '10 mg', 'frequency' => 'Daily',
            ])->assertForbidden();

        $this->assertSame(0, Prescription::query()->count());
    }
}
