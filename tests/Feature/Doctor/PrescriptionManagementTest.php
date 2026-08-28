<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionManagementTest extends TestCase
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

    public function test_doctor_can_add_a_multi_medicine_prescription_and_view_it_grouped_by_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->post(route('doctor.appointments.prescription.store', $appointment), [
                'notes' => 'Review in 2 weeks',
                'items' => [
                    ['medicine_name' => 'Sertraline', 'dosage' => '50 mg', 'frequency' => 'Once daily', 'duration' => '30 days'],
                    ['medicine_name' => 'Lorazepam', 'dosage' => '1 mg', 'frequency' => 'At night', 'special_instructions' => 'Only if needed'],
                ],
            ])->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->user_id,
            'doctor_id' => $appointment->doctor_id,
            'clinic_id' => $appointment->medical_center_id,
            'notes' => 'Review in 2 weeks',
        ]);
        $this->assertDatabaseHas('prescription_items', ['medicine_name' => 'Sertraline', 'dosage' => '50 mg']);
        $this->assertDatabaseHas('prescription_items', ['medicine_name' => 'Lorazepam', 'special_instructions' => 'Only if needed']);

        $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.patients.show', $appointment->user))
            ->assertOk()
            ->assertSee('Medication History')
            ->assertSee('Sertraline')
            ->assertSee('Lorazepam');
    }

    public function test_saving_a_prescription_again_replaces_the_previous_items(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->post(route('doctor.appointments.prescription.store', $appointment), [
                'items' => [['medicine_name' => 'Sertraline', 'dosage' => '50 mg', 'frequency' => 'Once daily']],
            ]);

        $this->actingAs($appointment->doctor, 'doctor')
            ->post(route('doctor.appointments.prescription.store', $appointment), [
                'items' => [['medicine_name' => 'Fluoxetine', 'dosage' => '20 mg', 'frequency' => 'Once daily']],
            ]);

        $this->assertSame(1, Prescription::query()->count());
        $this->assertSame(1, PrescriptionItem::query()->count());
        $this->assertDatabaseMissing('prescription_items', ['medicine_name' => 'Sertraline']);
        $this->assertDatabaseHas('prescription_items', ['medicine_name' => 'Fluoxetine']);
    }

    public function test_prescription_requires_at_least_one_item(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->post(route('doctor.appointments.prescription.store', $appointment), ['items' => []])
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Prescription::query()->count());
    }

    public function test_doctor_cannot_add_a_prescription_to_another_doctors_appointment(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs(Doctor::factory()->create(), 'doctor')
            ->post(route('doctor.appointments.prescription.store', $appointment), [
                'items' => [['medicine_name' => 'Medication', 'dosage' => '10 mg', 'frequency' => 'Daily']],
            ])->assertForbidden();

        $this->assertSame(0, Prescription::query()->count());
    }

    public function test_doctor_can_download_the_prescription_pdf(): void
    {
        $appointment = Appointment::factory()->create();
        Prescription::factory()->create(['appointment_id' => $appointment->id, 'doctor_id' => $appointment->doctor_id, 'patient_id' => $appointment->user_id]);

        $response = $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.appointments.prescription.download', $appointment));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_download_returns_404_when_no_prescription_exists(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.appointments.prescription.download', $appointment))
            ->assertNotFound();
    }

    public function test_doctor_cannot_download_another_doctors_prescription(): void
    {
        $appointment = Appointment::factory()->create();
        Prescription::factory()->create(['appointment_id' => $appointment->id, 'doctor_id' => $appointment->doctor_id, 'patient_id' => $appointment->user_id]);

        $this->actingAs(Doctor::factory()->create(), 'doctor')
            ->get(route('doctor.appointments.prescription.download', $appointment))
            ->assertForbidden();
    }
}
