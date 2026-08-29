<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\ClinicStaff;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicStaffAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/medical-center/staff/login');

        $response->assertStatus(200);
    }

    public function test_active_staff_can_login_and_reach_dashboard(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->post('/medical-center/staff/login', [
            'email' => $staff->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($staff, 'clinic_staff');
        $response->assertRedirect(route('medical-center.dashboard'));

        $this->get(route('medical-center.dashboard'))->assertOk();
    }

    public function test_disabled_staff_cannot_login(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->disabled()->create();

        $response = $this->from('/medical-center/staff/login')->post('/medical-center/staff/login', [
            'email' => $staff->email,
            'password' => 'password',
        ]);

        $this->assertGuest('clinic_staff');
        $response->assertSessionHasErrors('email');
    }

    public function test_staff_sees_same_dashboard_data_as_primary_clinic_account(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create(['clinic_id' => $clinic->id, 'status' => 'active']);
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.dashboard'));

        $response->assertOk()->assertSee($doctor->name);
    }

    public function test_staff_from_one_clinic_cannot_see_another_clinics_appointments(): void
    {
        $clinicA = MedicalCenter::factory()->approved()->create();
        $clinicB = MedicalCenter::factory()->approved()->create();
        $staffOfB = ClinicStaff::factory()->for($clinicB, 'medicalCenter')->create();

        $response = $this->actingAs($staffOfB, 'clinic_staff')->get(route('medical-center.appoinment-managment.index'));

        $response->assertOk()->assertDontSee($clinicA->name);
    }

    public function test_primary_medical_center_login_still_works_unaffected(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();

        $response = $this->post('/medical-center/login', [
            'email' => $medicalCenter->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($medicalCenter, 'medical_center');
        $response->assertRedirect(route('medical-center.dashboard'));
    }
}
