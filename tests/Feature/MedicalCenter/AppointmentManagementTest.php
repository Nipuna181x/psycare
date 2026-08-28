<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_center_can_view_its_appointments(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $medicalCenter->id]);

        $response = $this->actingAs($medicalCenter, 'medical_center')->get(route('medical-center.appoinment-managment.index'));

        $response->assertOk();
    }

    public function test_medical_center_cannot_view_another_clinics_appointment(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $otherAppointment = Appointment::factory()->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')->get(route('medical-center.appoinment-managment.show', $otherAppointment));

        $response->assertStatus(403);
    }
}
