<?php

namespace Tests\Feature;

use App\Models\TherapyRoom;
use App\Models\User;
use App\Services\TherapyParticipantAssigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TherapyRoomParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_patient_sees_only_their_own_label(): void
    {
        $room = TherapyRoom::factory()->create();
        $patient = User::factory()->create();
        $otherPatient = User::factory()->create(['name' => 'Someone Else']);

        $assigner = app(TherapyParticipantAssigner::class);
        $assigner->handle($room, $patient);
        $assigner->handle($room, $otherPatient);

        $response = $this->actingAs($patient)->get(route('therapy-rooms.show', $room));

        $response->assertOk();
        $response->assertSee('Patient A');
        $response->assertDontSee('Someone Else');
        $response->assertDontSee($otherPatient->email);
    }

    public function test_unassigned_patient_cannot_view_room(): void
    {
        $room = TherapyRoom::factory()->create();
        $patient = User::factory()->create();

        $response = $this->actingAs($patient)->get(route('therapy-rooms.show', $room));

        $response->assertStatus(403);
    }

    public function test_unassigned_patient_cannot_join_session(): void
    {
        $room = TherapyRoom::factory()->live()->create();
        $patient = User::factory()->create();

        $response = $this->actingAs($patient)->get(route('therapy-rooms.session', $room));

        $response->assertStatus(403);
    }

    public function test_patient_cannot_join_a_completed_room(): void
    {
        $room = TherapyRoom::factory()->completed()->create();
        $patient = User::factory()->create();
        app(TherapyParticipantAssigner::class)->handle($room, $patient);

        $response = $this->actingAs($patient)->get(route('therapy-rooms.session', $room));

        $response->assertStatus(403);
    }

    public function test_patient_cannot_join_a_cancelled_room(): void
    {
        $room = TherapyRoom::factory()->cancelled()->create();
        $patient = User::factory()->create();
        app(TherapyParticipantAssigner::class)->handle($room, $patient);

        $response = $this->actingAs($patient)->get(route('therapy-rooms.show', $room));

        $response->assertStatus(403);
    }
}
