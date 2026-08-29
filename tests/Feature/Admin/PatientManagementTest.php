<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_view_patient_details(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create(['name' => 'Searchable Patient']);
        User::factory()->create(['name' => 'Someone Else']);

        $this->actingAs($admin, 'admin')->get(route('admin.patients.index', ['search' => 'Searchable']))
            ->assertOk()->assertSee('Searchable Patient')->assertDontSee('Someone Else');
        $this->actingAs($admin, 'admin')->get(route('admin.patients.show', $patient))
            ->assertOk()->assertSee('Searchable Patient');
    }

    public function test_admin_can_suspend_and_restore_a_patient(): void
    {
        $admin = Admin::factory()->create();
        $patient = User::factory()->create();

        $this->actingAs($admin, 'admin')->patch(route('admin.patients.ban', $patient))->assertRedirect();
        $this->assertTrue($patient->fresh()->is_banned);
        $this->assertNotNull($patient->fresh()->banned_at);

        $this->actingAs($admin, 'admin')->patch(route('admin.patients.restore', $patient))->assertRedirect();
        $this->assertFalse($patient->fresh()->is_banned);
        $this->assertNull($patient->fresh()->banned_at);
    }

    public function test_suspended_patient_cannot_sign_in_or_use_authenticated_pages(): void
    {
        $patient = User::factory()->banned()->create();

        $this->post(route('login'), ['email' => $patient->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($patient)->get(route('appointments.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
