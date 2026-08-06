<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_patient_can_register_with_name_email_mobile_and_password(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane Patient',
            'email' => 'jane@example.com',
            'mobile' => '0712345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'mobile' => '0712345678',
        ]);

        $this->assertAuthenticated('web');
        $response->assertRedirect(route('home'));
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'jane@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Jane Patient',
            'email' => 'jane@example.com',
            'mobile' => '0712345678',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Jane Patient',
            'email' => 'jane@example.com',
            'mobile' => '0712345678',
            'password' => 'password',
            'password_confirmation' => 'wrong',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest('web');
    }
}
