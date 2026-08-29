<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Appointment;
use App\Models\ClinicStaff;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_can_view_its_own_appointment_detail(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $clinic->id,
            'patient_name' => 'Amaya Silva',
            'requires_immediate_escalation' => true,
        ]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $appointment));

        $response->assertOk()
            ->assertSee('Amaya Silva')
            ->assertSee('PHQ-9')
            ->assertSee('Immediate clinical review required');
    }

    public function test_clinic_cannot_view_another_clinics_appointment(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherAppointment = Appointment::factory()->create();

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $otherAppointment));

        $response->assertStatus(403);
    }

    public function test_clinic_sees_cancel_button_only_when_confirmed(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $confirmed = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'status' => 'confirmed']);
        $completed = Appointment::factory()->for($doctor)->completed()->create(['medical_center_id' => $clinic->id]);

        $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $confirmed))
            ->assertSee('Cancel appointment');

        $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $completed))
            ->assertDontSee('Cancel appointment');
    }

    public function test_clinic_can_cancel_a_confirmed_appointment(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'status' => 'confirmed']);

        $response = $this->actingAs($clinic, 'medical_center')->patch(route('medical-center.appoinment-managment.status', $appointment), [
            'status' => 'cancelled',
        ]);

        $response->assertRedirect();
        $this->assertSame('cancelled', $appointment->fresh()->status);
    }

    public function test_clinic_cannot_mark_an_appointment_completed(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'status' => 'confirmed']);

        $response = $this->actingAs($clinic, 'medical_center')->patch(route('medical-center.appoinment-managment.status', $appointment), [
            'status' => 'completed',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_clinic_cannot_cancel_another_clinics_appointment(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherAppointment = Appointment::factory()->create(['status' => 'confirmed']);

        $response = $this->actingAs($clinic, 'medical_center')->patch(route('medical-center.appoinment-managment.status', $otherAppointment), [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(403);
        $this->assertSame('confirmed', $otherAppointment->fresh()->status);
    }

    public function test_clinic_sees_no_prescription_edit_form(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);
        Prescription::factory()->for($appointment)->create();

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $appointment));

        $response->assertOk()
            ->assertDontSee('name="items[0][medicine_name]"', false)
            ->assertDontSee('Save prescription');
    }

    public function test_clinic_sees_read_only_prescription_details_when_present(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);
        $prescription = Prescription::factory()->for($appointment)->create();
        $item = $prescription->items()->first();

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $appointment));

        $response->assertOk()->assertSee($item->medicine_name);
    }

    public function test_clinic_sees_empty_state_when_no_prescription(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.appoinment-managment.show', $appointment));

        $response->assertOk()->assertSee('No prescription recorded for this visit.');
    }

    public function test_staff_login_reaches_the_same_appointment_detail_page(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id]);
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.appoinment-managment.show', $appointment));

        $response->assertOk()->assertSee($appointment->patient_name);
    }
}
