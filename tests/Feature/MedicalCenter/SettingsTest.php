<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('medical-center.settings.edit'))->assertRedirect(route('medical-center.login'));
    }

    public function test_clinic_can_view_settings_page(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $this->actingAs($clinic, 'medical_center')
            ->get(route('medical-center.settings.edit'))
            ->assertOk()
            ->assertSee('Facility fee');
    }

    public function test_clinic_can_update_facility_fee(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create(['facility_fee' => 500]);

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.pricing.update'), ['facility_fee' => 1200])
            ->assertRedirect();

        $this->assertSame('1200.00', $clinic->fresh()->facility_fee);
    }

    public function test_pricing_update_rejects_a_negative_fee(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create(['facility_fee' => 500]);

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.pricing.update'), ['facility_fee' => -50])
            ->assertSessionHasErrors('facility_fee');

        $this->assertSame('500.00', $clinic->fresh()->facility_fee);
    }
}
