<?php

namespace Tests\Feature\Auth;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin, 'admin');
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_cannot_login_with_incorrect_password(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest('admin');
        $response->assertSessionHasErrors('email');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_logout(): void
    {
        $admin = Admin::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $this->assertGuest('admin');
        $response->assertRedirect(route('admin.login'));
    }
}
