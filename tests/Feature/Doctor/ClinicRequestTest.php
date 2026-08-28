<?php

namespace Tests\Feature\Doctor;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_view_clinic_requests(): void
    {
        $doctor = Doctor::factory()->create();
        $clinic = MedicalCenter::factory()->approved()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->requested()->create(['clinic_id' => $clinic->id]);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.clinic-requests.index'));

        $response->assertOk()->assertSee($clinic->name);
    }

    public function test_doctor_can_accept_a_request(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->requested()->create();

        $response = $this->actingAs($doctor, 'doctor')->patch(route('doctor.clinic-requests.accept', $affiliation));

        $response->assertRedirect();
        $this->assertDatabaseHas('doctor_clinic_affiliations', [
            'id' => $affiliation->id,
            'status' => 'active',
        ]);
    }

    public function test_doctor_can_decline_a_request(): void
    {
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->requested()->create();

        $response = $this->actingAs($doctor, 'doctor')->patch(route('doctor.clinic-requests.decline', $affiliation));

        $response->assertRedirect();
        $this->assertDatabaseHas('doctor_clinic_affiliations', [
            'id' => $affiliation->id,
            'status' => 'declined',
        ]);
    }

    public function test_doctor_cannot_respond_to_another_doctors_request(): void
    {
        $doctor = Doctor::factory()->create();
        $otherDoctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($otherDoctor)->requested()->create();

        $response = $this->actingAs($doctor, 'doctor')->patch(route('doctor.clinic-requests.accept', $affiliation));

        $response->assertStatus(403);
    }
}
