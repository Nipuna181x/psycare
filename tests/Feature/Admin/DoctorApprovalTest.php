<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_pending_doctor_applications(): void
    {
        $admin = Admin::factory()->create();
        Doctor::factory()->pendingApproval()->create(['onboarding_step' => 'profile_complete']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.doctor-approvals.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_a_pending_doctor(): void
    {
        $admin = Admin::factory()->create();
        $doctor = Doctor::factory()->pendingApproval()->create(['onboarding_step' => 'profile_complete']);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.doctor-approvals.approve', $doctor));

        $response->assertRedirect();
        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
        $this->assertNotNull($doctor->fresh()->approved_at);
    }

    public function test_admin_can_reject_a_pending_doctor(): void
    {
        $admin = Admin::factory()->create();
        $doctor = Doctor::factory()->pendingApproval()->create(['onboarding_step' => 'profile_complete']);

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.doctor-approvals.reject', $doctor));

        $response->assertRedirect();
        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'status' => 'rejected',
        ]);
    }

    public function test_guest_cannot_approve_doctors(): void
    {
        $doctor = Doctor::factory()->pendingApproval()->create(['onboarding_step' => 'profile_complete']);

        $response = $this->patch(route('admin.doctor-approvals.approve', $doctor));

        $response->assertRedirect(route('admin.login'));
    }
}
