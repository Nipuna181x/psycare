<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_and_search_doctors(): void
    {
        $admin = Admin::factory()->create();
        Doctor::factory()->pendingApproval()->create(['name' => 'Pending Specialist', 'onboarding_step' => 'profile_complete']);
        Doctor::factory()->create(['name' => 'Approved Specialist']);

        $this->actingAs($admin, 'admin')->get(route('admin.doctors.index'))
            ->assertOk()
            ->assertViewHas('status', 'all')
            ->assertSee('Pending Specialist')
            ->assertSee('Approved Specialist');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.doctors.index', [
            'status' => 'pending_approval',
            'search' => 'Pending',
        ]));

        $response->assertOk()->assertSee('Pending Specialist')->assertDontSee('Approved Specialist');
    }

    public function test_admin_can_view_a_doctor_detail_page(): void
    {
        $admin = Admin::factory()->create();
        $doctor = Doctor::factory()->create(['name' => 'Clinical Reviewer']);

        $this->actingAs($admin, 'admin')->get(route('admin.doctors.show', $doctor))
            ->assertOk()->assertSee('Clinical Reviewer')->assertSee($doctor->license_number);
    }

    public function test_doctor_with_incomplete_profile_cannot_be_approved(): void
    {
        $admin = Admin::factory()->create();
        $doctor = Doctor::factory()->pendingApproval()->create();

        $this->actingAs($admin, 'admin')->patch(route('admin.doctors.approve', $doctor))->assertSessionHasErrors('approval');
        $this->assertSame('pending_approval', $doctor->fresh()->status);
    }
}
