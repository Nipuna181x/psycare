<?php

namespace Tests\Feature\Auth;

use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalCenterRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/medical-center/register');

        $response->assertStatus(200);
    }

    public function test_medical_center_can_register_and_starts_pending(): void
    {
        $response = $this->post('/medical-center/register', [
            'name' => 'Hope Clinic',
            'email' => 'hope@example.com',
            'phone' => '0711234567',
            'address' => '123 Main St',
            'registration_number' => 'REG-0001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('medical_centers', [
            'email' => 'hope@example.com',
            'status' => 'pending',
        ]);

        // Registration does not auto-login, since the account still needs approval.
        $this->assertGuest('medical_center');
        $response->assertRedirect(route('medical-center.login'));
    }

    public function test_registration_requires_a_unique_registration_number(): void
    {
        MedicalCenter::factory()->create(['registration_number' => 'REG-0001']);

        $response = $this->from('/medical-center/register')->post('/medical-center/register', [
            'name' => 'Hope Clinic',
            'email' => 'hope@example.com',
            'phone' => '0711234567',
            'address' => '123 Main St',
            'registration_number' => 'REG-0001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('registration_number');
    }
}
