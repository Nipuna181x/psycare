<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_can_search_doctors_by_license_number(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['license_number' => 'SLMC-9999']);
        Doctor::factory()->create(['license_number' => 'SLMC-1111']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.find-doctors.index', ['license_number' => 'SLMC-9999']));

        $response->assertOk()->assertSee($doctor->name)->assertDontSee('SLMC-1111');
    }

    public function test_clinic_can_search_doctors_by_name(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Dr. Nadeesha Perera']);
        Doctor::factory()->create(['name' => 'Dr. Someone Else']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.find-doctors.index', ['name' => 'Nadeesha']));

        $response->assertOk()->assertSee($doctor->name)->assertDontSee('Dr. Someone Else');
    }

    public function test_search_excludes_doctors_pending_approval(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        Doctor::factory()->pendingApproval()->create(['name' => 'Dr. Pending', 'license_number' => 'SLMC-2222']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.find-doctors.index', ['license_number' => 'SLMC-2222']));

        $response->assertOk()->assertDontSee('Dr. Pending');
    }

    public function test_clinic_can_send_a_work_request(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($clinic, 'medical_center')
            ->post(route('medical-center.find-doctors.request', $doctor));

        $response->assertRedirect();
        $this->assertDatabaseHas('doctor_clinic_affiliations', [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'status' => 'requested',
        ]);
    }

    public function test_clinic_cannot_send_a_duplicate_request(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id, 'status' => 'requested']);

        $response = $this->actingAs($clinic, 'medical_center')
            ->post(route('medical-center.find-doctors.request', $doctor));

        $response->assertStatus(422);
    }
}
