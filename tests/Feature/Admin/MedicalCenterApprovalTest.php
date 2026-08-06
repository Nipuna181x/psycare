<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalCenterApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_medical_center_registrations(): void
    {
        $admin = Admin::factory()->create();
        MedicalCenter::factory()->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.user-managment.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_a_pending_medical_center(): void
    {
        $admin = Admin::factory()->create();
        $medicalCenter = MedicalCenter::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.user-managment.medical-centers.approve', $medicalCenter));

        $response->assertRedirect();
        $this->assertDatabaseHas('medical_centers', [
            'id' => $medicalCenter->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_a_pending_medical_center(): void
    {
        $admin = Admin::factory()->create();
        $medicalCenter = MedicalCenter::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->patch(route('admin.user-managment.medical-centers.reject', $medicalCenter));

        $response->assertRedirect();
        $this->assertDatabaseHas('medical_centers', [
            'id' => $medicalCenter->id,
            'status' => 'rejected',
        ]);
    }

    public function test_guest_cannot_approve_medical_centers(): void
    {
        $medicalCenter = MedicalCenter::factory()->create();

        $response = $this->patch(route('admin.user-managment.medical-centers.approve', $medicalCenter));

        $response->assertRedirect(route('admin.login'));
    }
}
