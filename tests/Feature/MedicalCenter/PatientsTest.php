<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Appointment;
use App\Models\ClinicStaff;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_sees_patients_with_appointments_deduplicated(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create(['name' => 'Amaya Silva']);
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $clinic->id]);
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.patients.index'));

        $response->assertOk()->assertSeeInOrder(['Amaya Silva'])->assertSee('2 appointment(s)');
    }

    public function test_clinic_does_not_see_patient_from_another_clinic_only(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create(['name' => 'Other Clinic Patient']);
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $otherClinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.patients.index'));

        $response->assertOk()->assertDontSee('Other Clinic Patient');
    }

    public function test_name_filter_narrows_results(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $matching = User::factory()->create(['name' => 'Nadeesha Perera']);
        $other = User::factory()->create(['name' => 'Someone Else']);
        Appointment::factory()->for($doctor)->for($matching)->create(['medical_center_id' => $clinic->id]);
        Appointment::factory()->for($doctor)->for($other)->create(['medical_center_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.patients.index', ['name' => 'Nadeesha']));

        $response->assertOk()->assertSee('Nadeesha Perera')->assertDontSee('Someone Else');
    }

    public function test_doctor_filter_narrows_results(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctorA = Doctor::factory()->create();
        $doctorB = Doctor::factory()->create();
        $patientA = User::factory()->create(['name' => 'Patient Of A']);
        $patientB = User::factory()->create(['name' => 'Patient Of B']);
        Appointment::factory()->for($doctorA)->for($patientA)->create(['medical_center_id' => $clinic->id]);
        Appointment::factory()->for($doctorB)->for($patientB)->create(['medical_center_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.patients.index', ['doctor_id' => $doctorA->id]));

        $response->assertOk()->assertSee('Patient Of A')->assertDontSee('Patient Of B');
    }

    public function test_patient_detail_shows_only_appointments_at_this_clinic(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $clinic->id, 'reason' => 'Visit at this clinic']);
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $otherClinic->id, 'reason' => 'Visit at other clinic']);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.patients.show', $patient));

        $response->assertOk()->assertSee('Visit at this clinic')->assertDontSee('Visit at other clinic');
    }

    public function test_clinic_cannot_view_patient_with_zero_appointments_here(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $otherClinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.patients.show', $patient));

        $response->assertStatus(403);
    }

    public function test_staff_login_sees_identical_patients_list(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $patient = User::factory()->create(['name' => 'Shared Patient']);
        Appointment::factory()->for($doctor)->for($patient)->create(['medical_center_id' => $clinic->id]);
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.patients.index'));

        $response->assertOk()->assertSee('Shared Patient');
    }
}
