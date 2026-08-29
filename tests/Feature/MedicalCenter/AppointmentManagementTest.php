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

    public function test_name_filter_narrows_appointments(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $medicalCenter->id, 'patient_name' => 'Nadeesha Perera']);
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $medicalCenter->id, 'patient_name' => 'Someone Else']);

        $response = $this->actingAs($medicalCenter, 'medical_center')
            ->get(route('medical-center.appoinment-managment.index', ['name' => 'Nadeesha']));

        $response->assertOk()->assertSee('Nadeesha Perera')->assertDontSee('Someone Else');
    }

    public function test_date_range_filter_narrows_appointments(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $medicalCenter->id,
            'patient_name' => 'In Range Patient',
            'appointment_date' => now()->addDays(2)->toDateString(),
        ]);
        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $medicalCenter->id,
            'patient_name' => 'Out Of Range Patient',
            'appointment_date' => now()->addDays(20)->toDateString(),
        ]);

        $response = $this->actingAs($medicalCenter, 'medical_center')->get(route('medical-center.appoinment-managment.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->addDays(5)->toDateString(),
        ]));

        $response->assertOk()->assertSee('In Range Patient')->assertDontSee('Out Of Range Patient');
    }
}
