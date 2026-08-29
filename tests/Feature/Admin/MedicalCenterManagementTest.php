<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalCenterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_and_search_medical_centers(): void
    {
        $admin = Admin::factory()->create();
        MedicalCenter::factory()->create(['name' => 'Pending Wellness Center', 'status' => 'pending']);
        MedicalCenter::factory()->approved()->create(['name' => 'Approved Care Center']);

        $this->actingAs($admin, 'admin')->get(route('admin.medical-centers.index'))
            ->assertOk()
            ->assertViewHas('status', 'all')
            ->assertSee('Pending Wellness Center')
            ->assertSee('Approved Care Center');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.medical-centers.index', [
            'status' => 'pending',
            'search' => 'Wellness',
        ]));

        $response->assertOk()->assertSee('Pending Wellness Center')->assertDontSee('Approved Care Center');
    }

    public function test_admin_can_view_a_medical_center_detail_page(): void
    {
        $admin = Admin::factory()->create();
        $medicalCenter = MedicalCenter::factory()->approved()->create(['name' => 'PsyCare Central']);

        $this->actingAs($admin, 'admin')->get(route('admin.medical-centers.show', $medicalCenter))
            ->assertOk()->assertSee('PsyCare Central')->assertSee($medicalCenter->registration_number);
    }
}
