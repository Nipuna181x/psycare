<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\ClinicStaff;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_clinic_can_create_a_staff_account(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $response = $this->actingAs($clinic, 'medical_center')->post(route('medical-center.staff.store'), [
            'name' => 'Reception Staff',
            'email' => 'reception@clinic.lk',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clinic_staff', [
            'medical_center_id' => $clinic->id,
            'email' => 'reception@clinic.lk',
            'status' => 'active',
        ]);
    }

    public function test_new_staff_account_can_immediately_login(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $this->actingAs($clinic, 'medical_center')->post(route('medical-center.staff.store'), [
            'name' => 'Reception Staff',
            'email' => 'reception@clinic.lk',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response = $this->post('/medical-center/staff/login', [
            'email' => 'reception@clinic.lk',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated('clinic_staff');
        $response->assertRedirect(route('medical-center.dashboard'));
    }

    public function test_primary_clinic_can_remove_staff_access(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($clinic, 'medical_center')->delete(route('medical-center.staff.destroy', $staff));

        $response->assertRedirect();
        $this->assertSame('disabled', $staff->fresh()->status);
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

    public function test_clinic_cannot_manage_another_clinics_staff(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $otherStaff = ClinicStaff::factory()->for($otherClinic, 'medicalCenter')->create();

        $response = $this->actingAs($clinic, 'medical_center')->delete(route('medical-center.staff.destroy', $otherStaff));

        $response->assertStatus(403);
        $this->assertSame('active', $otherStaff->fresh()->status);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        ClinicStaff::factory()->for($clinic, 'medicalCenter')->create(['email' => 'taken@clinic.lk']);

        $response = $this->actingAs($clinic, 'medical_center')->post(route('medical-center.staff.store'), [
            'name' => 'Another Person',
            'email' => 'taken@clinic.lk',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_staff_authenticated_request_is_forbidden_from_managing_staff(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();
        $otherStaff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.staff.index'))->assertStatus(403);
        $this->actingAs($staff, 'clinic_staff')->post(route('medical-center.staff.store'), [
            'name' => 'X', 'email' => 'x@clinic.lk', 'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertStatus(403);
        $this->actingAs($staff, 'clinic_staff')->delete(route('medical-center.staff.destroy', $otherStaff))->assertStatus(403);
    }

    public function test_staff_sidebar_does_not_render_clinic_staff_link(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.dashboard'));

        $response->assertOk()->assertDontSee('Clinic Staff');
    }
}
