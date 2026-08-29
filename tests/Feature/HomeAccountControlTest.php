<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeAccountControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_account_label_and_registration_icon(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Sign in or sign up')
            ->assertSee(route('register'))
            ->assertSee('href="'.route('therapy-rooms.index').'"', false)
            ->assertDontSee('>Book a doctor</a>', false);
    }

    public function test_authenticated_patient_sees_first_name_and_account_dropdown(): void
    {
        $patient = User::factory()->create([
            'name' => 'Maya Fernando',
            'email' => 'maya@example.test',
        ]);

        $this->actingAs($patient)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Maya')
            ->assertSee('maya@example.test')
            ->assertSee('Settings')
            ->assertSee('Sign out');
    }
}
