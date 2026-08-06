<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_patient_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user, 'web');
        $response->assertRedirect(route('home'));
    }

    public function test_patient_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('web');
        $response->assertSessionHasErrors('email');
    }

    public function test_patient_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'web')->post('/logout');

        $this->assertGuest('web');
        $response->assertRedirect(route('home'));
    }
}
