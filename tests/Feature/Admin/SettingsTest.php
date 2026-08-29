<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_profile_information(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->patch(route('admin.settings.profile.update'), [
            'name' => 'Platform Administrator',
            'email' => 'platform@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('admins', ['id' => $admin->id, 'name' => 'Platform Administrator', 'email' => 'platform@example.com']);
    }

    public function test_admin_can_update_password_with_current_password(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->patch(route('admin.settings.password.update'), [
            'current_password' => 'password',
            'password' => 'A-new-secure-password1',
            'password_confirmation' => 'A-new-secure-password1',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('A-new-secure-password1', $admin->fresh()->password));
    }
}
