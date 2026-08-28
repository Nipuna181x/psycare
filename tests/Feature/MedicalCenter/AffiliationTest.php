<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_can_view_requests_it_has_sent(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id, 'status' => 'requested']);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.affiliations.index'));

        $response->assertOk()->assertSee($doctor->name);
    }

    public function test_clinic_only_sees_its_own_requests(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create(['name' => 'Dr. Other Clinic Doctor']);
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $otherClinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.affiliations.index'));

        $response->assertOk()->assertDontSee('Dr. Other Clinic Doctor');
    }
}
