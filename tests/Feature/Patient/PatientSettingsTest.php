<?php

namespace Tests\Feature\Patient;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PatientSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('settings.index'))->assertRedirect(route('login'));
    }

    public function test_patient_can_view_settings_page(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Settings')
            ->assertSee($patient->name);
    }

    public function test_patient_can_update_profile(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)
            ->patch(route('settings.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.test',
                'mobile' => '0771234567',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.test',
            'mobile' => '0771234567',
        ]);
        $this->assertSame('updated@example.test', $patient->fresh()->routeNotificationFor('mail'));
    }

    public function test_profile_update_rejects_an_email_already_used_by_another_patient(): void
    {
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($patient)
            ->patch(route('settings.profile.update'), [
                'name' => $patient->name,
                'email' => 'taken@example.test',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['id' => $patient->id, 'email' => 'taken@example.test']);
    }

    public function test_patient_can_update_password(): void
    {
        $patient = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($patient)
            ->patch(route('settings.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-secure-password', $patient->fresh()->password));
    }

    public function test_password_update_requires_correct_current_password(): void
    {
        $patient = User::factory()->create(['password' => Hash::make('old-password')]);

        $this->actingAs($patient)
            ->patch(route('settings.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $patient->fresh()->password));
    }
}
