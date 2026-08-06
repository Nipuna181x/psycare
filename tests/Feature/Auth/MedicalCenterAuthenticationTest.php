<?php

namespace Tests\Feature\Auth;

use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalCenterAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/medical-center/login');

        $response->assertStatus(200);
    }

    public function test_approved_medical_center_can_login(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();

        $response = $this->post('/medical-center/login', [
            'email' => $medicalCenter->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($medicalCenter, 'medical_center');
        $response->assertRedirect(route('medical-center.dashboard'));
    }

    public function test_pending_medical_center_cannot_login(): void
    {
        $medicalCenter = MedicalCenter::factory()->create();

        $response = $this->from('/medical-center/login')->post('/medical-center/login', [
            'email' => $medicalCenter->email,
            'password' => 'password',
        ]);

        $this->assertGuest('medical_center');
        $response->assertSessionHasErrors('email');
    }

    public function test_rejected_medical_center_cannot_login(): void
    {
        $medicalCenter = MedicalCenter::factory()->rejected()->create();

        $response = $this->from('/medical-center/login')->post('/medical-center/login', [
            'email' => $medicalCenter->email,
            'password' => 'password',
        ]);

        $this->assertGuest('medical_center');
        $response->assertSessionHasErrors('email');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/medical-center/dashboard');

        $response->assertRedirect(route('medical-center.login'));
    }

    public function test_medical_center_can_logout(): void
    {
        $medicalCenter = MedicalCenter::factory()->approved()->create();

        $response = $this->actingAs($medicalCenter, 'medical_center')->post('/medical-center/logout');

        $this->assertGuest('medical_center');
        $response->assertRedirect(route('medical-center.login'));
    }
}
