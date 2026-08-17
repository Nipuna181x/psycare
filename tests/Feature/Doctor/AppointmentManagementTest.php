<?php

namespace Tests\Feature\Doctor;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_view_own_appointments(): void
    {
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $doctor->medical_center_id]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.index'));

        $response->assertOk();
    }

    public function test_doctor_cannot_view_another_doctors_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $otherAppointment = Appointment::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.appointments.show', $otherAppointment));

        $response->assertStatus(403);
    }

    public function test_doctor_can_mark_appointment_completed(): void
    {
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $doctor->medical_center_id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($doctor, 'doctor')
            ->patch(route('doctor.appointments.status', $appointment), ['status' => 'completed']);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'completed']);
    }

    public function test_doctor_cannot_update_another_doctors_appointment_status(): void
    {
        $doctor = Doctor::factory()->create();
        $otherAppointment = Appointment::factory()->create(['status' => 'confirmed']);

        $response = $this->actingAs($doctor, 'doctor')
            ->patch(route('doctor.appointments.status', $otherAppointment), ['status' => 'completed']);

        $response->assertStatus(403);
    }
}
