<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\ClinicStaff;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertSee('Facility fee')
            ->assertSee('columns-1 gap-5 xl:columns-2', false)
            ->assertSee('Settings');
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

    public function test_clinic_can_update_name_and_description(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.profile.update'), [
                'name' => 'Serene Mind Clinic',
                'description' => 'A calm space for care.',
            ])
            ->assertRedirect();

        $clinic->refresh();
        $this->assertSame('Serene Mind Clinic', $clinic->name);
        $this->assertSame('A calm space for care.', $clinic->description);
    }

    public function test_clinic_can_update_phone_and_address(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.contact.update'), [
                'phone' => '+94 11 234 5678',
                'address' => '123 Galle Road, Colombo',
            ])
            ->assertRedirect();

        $clinic->refresh();
        $this->assertSame('+94 11 234 5678', $clinic->phone);
        $this->assertSame('123 Galle Road, Colombo', $clinic->address);
    }

    public function test_clinic_can_upload_a_logo(): void
    {
        Storage::fake('public');
        $clinic = MedicalCenter::factory()->approved()->create();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.logo.update'), [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertRedirect();

        $clinic->refresh();
        $this->assertNotNull($clinic->logo_path);
        Storage::disk('public')->assertExists($clinic->logo_path);
    }

    public function test_clinic_can_update_operating_hours(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $hours = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])
            ->map(fn ($day) => ['day' => $day, 'opens' => '09:00', 'closes' => '17:00', 'closed' => $day === 'Sunday'])
            ->all();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.hours.update'), ['hours' => $hours])
            ->assertRedirect();

        $stored = $clinic->fresh()->operating_hours;
        $this->assertCount(7, $stored);
        $this->assertSame('09:00', $stored[0]['opens']);
        $this->assertTrue($stored[6]['closed']);
    }

    public function test_invalid_hours_payload_is_rejected(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.hours.update'), ['hours' => [['day' => 'Monday']]])
            ->assertSessionHasErrors('hours');

        $this->assertNull($clinic->fresh()->operating_hours);
    }

    public function test_open_day_requires_a_closing_time_after_its_opening_time(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $hours = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])
            ->map(fn ($day) => [
                'day' => $day,
                'opens' => $day === 'Monday' ? '17:00' : '09:00',
                'closes' => $day === 'Monday' ? '17:00' : '17:00',
                'closed' => false,
            ])
            ->all();

        $this->actingAs($clinic, 'medical_center')
            ->patch(route('medical-center.settings.hours.update'), ['hours' => $hours])
            ->assertSessionHasErrors('hours');

        $this->assertNull($clinic->fresh()->operating_hours);
    }

    public function test_staff_can_also_update_settings(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $this->actingAs($staff, 'clinic_staff')
            ->patch(route('medical-center.settings.profile.update'), [
                'name' => 'Updated By Staff',
                'description' => null,
            ])
            ->assertRedirect();

        $this->assertSame('Updated By Staff', $clinic->fresh()->name);
    }
}
