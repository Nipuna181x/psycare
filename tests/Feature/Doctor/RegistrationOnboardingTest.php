<?php

namespace Tests\Feature\Doctor;

use App\Models\Admin;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('doctor.register'));

        $response->assertStatus(200)->assertSee('Step 1 of 2');
    }

    public function test_doctor_can_register_and_is_logged_in_pending_approval(): void
    {
        $response = $this->post(route('doctor.register'), [
            'name' => 'Dr. Amaya Silva',
            'email' => 'amaya@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'license_number' => 'SLMC-1234',
            'phone' => '0771234567',
        ]);

        $doctor = Doctor::where('email', 'amaya@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($doctor, 'doctor');
        $this->assertSame('pending_approval', $doctor->status);
        $this->assertSame('basic_info_done', $doctor->onboarding_step);
        $response->assertRedirect(route('doctor.dashboard'));
    }

    public function test_doctor_with_incomplete_onboarding_is_redirected_to_onboarding_step(): void
    {
        $doctor = Doctor::factory()->pendingApproval()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertRedirect(route('doctor.onboarding.edit'));
    }

    public function test_doctor_can_complete_onboarding_and_is_then_sent_to_pending_page(): void
    {
        $doctor = Doctor::factory()->pendingApproval()->create();

        $response = $this->actingAs($doctor, 'doctor')->patch(route('doctor.onboarding.update'), [
            'specialization' => 'Clinical Psychology',
            'bio' => 'A short bio.',
            'years_of_experience' => 5,
        ]);

        $response->assertRedirect(route('doctor.dashboard'));
        $doctor->refresh();
        $this->assertSame('profile_complete', $doctor->onboarding_step);
        $this->assertSame('Clinical Psychology', $doctor->specialization);

        $pendingResponse = $this->get(route('doctor.dashboard'));
        $pendingResponse->assertRedirect(route('doctor.pending'));
    }

    public function test_approved_doctor_reaches_dashboard(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertStatus(200);
    }

    public function test_rejected_doctor_is_blocked(): void
    {
        $doctor = Doctor::factory()->create(['status' => 'rejected']);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertRedirect(route('doctor.blocked'));
    }

    public function test_suspended_doctor_is_blocked(): void
    {
        $doctor = Doctor::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.dashboard'));

        $response->assertRedirect(route('doctor.blocked'));
    }

    public function test_full_registration_to_approval_flow(): void
    {
        $this->post(route('doctor.register'), [
            'name' => 'Dr. Nadeesha Perera',
            'email' => 'nadeesha@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'license_number' => 'SLMC-5678',
        ]);

        $doctor = Doctor::where('email', 'nadeesha@example.com')->firstOrFail();

        $this->patch(route('doctor.onboarding.update'), [
            'specialization' => 'Psychiatry',
        ]);

        $this->get(route('doctor.dashboard'))->assertRedirect(route('doctor.pending'));

        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin')->patch(route('admin.doctor-approvals.approve', $doctor));

        $this->assertSame('approved', $doctor->fresh()->status);

        $this->post(route('doctor.logout'));

        $loginResponse = $this->post(route('doctor.login'), [
            'email' => 'nadeesha@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('doctor.dashboard'));
        $this->get(route('doctor.dashboard'))->assertStatus(200);
    }
}
