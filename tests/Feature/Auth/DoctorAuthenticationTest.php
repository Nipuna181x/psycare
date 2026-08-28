<?php

namespace Tests\Feature\Auth;

use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered_at_expected_url(): void
    {
        $response = $this->get('/doctor/login');

        $response->assertStatus(200)
            ->assertSee('Welcome back, doctor')
            ->assertSee('Continue to doctor portal')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);
    }

    public function test_doctor_can_login_with_email_and_password(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->post('/doctor/login', [
            'email' => $doctor->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($doctor, 'doctor');
        $response->assertRedirect(route('doctor.dashboard'));
    }

    public function test_doctor_cannot_login_with_incorrect_password(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->from('/doctor/login')->post('/doctor/login', [
            'email' => $doctor->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('doctor');
        $response->assertSessionHasErrors('email');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/doctor/dashboard');

        $response->assertRedirect(route('doctor.login'));
    }

    public function test_doctor_can_logout(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->post('/doctor/logout');

        $this->assertGuest('doctor');
        $response->assertRedirect(route('doctor.login'));
    }
}
