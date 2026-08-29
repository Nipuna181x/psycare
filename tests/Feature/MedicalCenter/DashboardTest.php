<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_can_view_its_dashboard(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id, 'status' => 'active']);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.dashboard'));

        $response->assertOk()->assertSee($doctor->name);
    }

    public function test_dashboard_renders_with_no_affiliations(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.dashboard'));

        $response->assertOk()->assertDontSee('Grow your team');
    }
}
