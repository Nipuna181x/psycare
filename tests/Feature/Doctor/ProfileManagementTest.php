<?php

namespace Tests\Feature\Doctor;

use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_and_updates_existing_profile_fields(): void
    {
        Storage::fake('public');
        $doctor = Doctor::factory()->create();

        $this->actingAs($doctor, 'doctor')->get(route('doctor.profile.edit'))->assertOk()->assertSee('Profile &amp; Settings', false);

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.profile.information.update'), [
            'name' => 'Dr. Maya Fernando',
            'specialization' => 'Psychiatry',
            'bio' => 'Trauma-informed psychiatrist.',
            'profile_photo' => UploadedFile::fake()->image('profile.jpg'),
        ])->assertRedirect();

        $doctor->refresh();
        $this->assertSame('Dr. Maya Fernando', $doctor->name);
        Storage::disk('public')->assertExists($doctor->avatar);
    }

    public function test_doctor_can_update_contact_and_password(): void
    {
        $doctor = Doctor::factory()->create(['password' => 'password']);

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.profile.contact.update'), [
            'email' => 'doctor@example.test', 'phone' => '0712345678',
        ])->assertRedirect();

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.profile.password.update'), [
            'current_password' => 'password', 'password' => 'NewSecurePass123!', 'password_confirmation' => 'NewSecurePass123!',
        ])->assertRedirect();

        $doctor->refresh();
        $this->assertSame('doctor@example.test', $doctor->email);
        $this->assertTrue(Hash::check('NewSecurePass123!', $doctor->password));
    }

    public function test_incorrect_current_password_is_rejected(): void
    {
        $doctor = Doctor::factory()->create(['password' => 'password']);

        $this->actingAs($doctor, 'doctor')->from(route('doctor.profile.edit'))->patch(route('doctor.profile.password.update'), [
            'current_password' => 'incorrect', 'password' => 'NewSecurePass123!', 'password_confirmation' => 'NewSecurePass123!',
        ])->assertSessionHasErrors('current_password');
    }
}
