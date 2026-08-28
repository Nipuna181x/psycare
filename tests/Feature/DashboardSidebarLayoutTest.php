<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSidebarLayoutTest extends TestCase
{
    public function test_each_dashboard_uses_a_viewport_sidebar_and_independent_desktop_content_scroll(): void
    {
        $responses = [
            $this->actingAs(Admin::factory()->create(), 'admin')->get(route('admin.dashboard')),
            $this->actingAs(MedicalCenter::factory()->approved()->create(), 'medical_center')->get(route('medical-center.dashboard')),
            $this->actingAs(Doctor::factory()->create(), 'doctor')->get(route('doctor.dashboard')),
        ];

        foreach ($responses as $response) {
            $response->assertOk()
                ->assertSee('lg:h-dvh lg:overflow-hidden', false)
                ->assertSee('lg:sticky lg:top-5 lg:h-[calc(100dvh-2.5rem)] lg:overflow-y-auto', false)
                ->assertSee('lg:h-[calc(100dvh-2.5rem)] lg:overflow-y-auto', false);
        }
    }
}
