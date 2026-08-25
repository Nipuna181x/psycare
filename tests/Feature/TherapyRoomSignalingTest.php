<?php

namespace Tests\Feature;

use App\Events\TherapyRoomSignal;
use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\User;
use App\Services\TherapyParticipantAssigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TherapyRoomSignalingTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_participant_can_signal_another_participant(): void
    {
        Event::fake();

        $room = TherapyRoom::factory()->live()->create();
        $patientA = User::factory()->create();
        $patientB = User::factory()->create();
        $assigner = app(TherapyParticipantAssigner::class);
        $assigner->handle($room, $patientA);
        $assigner->handle($room, $patientB);

        $response = $this->actingAs($patientA)->postJson(route('therapy-rooms.signal', $room), [
            'to' => 'patient-'.$patientB->id,
            'type' => 'offer',
            'payload' => ['sdp' => 'fake-sdp', 'type' => 'offer'],
        ]);

        $response->assertOk();
        Event::assertDispatched(TherapyRoomSignal::class, fn (TherapyRoomSignal $event) => $event->therapyRoomId === $room->id
            && $event->to === 'patient-'.$patientB->id
            && $event->from === 'patient-'.$patientA->id
        );
    }

    public function test_non_participant_cannot_signal(): void
    {
        Event::fake();

        $room = TherapyRoom::factory()->live()->create();
        $patient = User::factory()->create();
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)->postJson(route('therapy-rooms.signal', $room), [
            'to' => 'patient-'.$patient->id,
            'type' => 'offer',
            'payload' => ['sdp' => 'fake-sdp', 'type' => 'offer'],
        ]);

        $response->assertStatus(403);
        Event::assertNotDispatched(TherapyRoomSignal::class);
    }

    public function test_cannot_signal_in_a_non_live_room(): void
    {
        Event::fake();

        $room = TherapyRoom::factory()->create();
        $patient = User::factory()->create();
        app(TherapyParticipantAssigner::class)->handle($room, $patient);

        $response = $this->actingAs($patient)->postJson(route('therapy-rooms.signal', $room), [
            'to' => 'doctor',
            'type' => 'offer',
            'payload' => ['sdp' => 'fake-sdp', 'type' => 'offer'],
        ]);

        $response->assertStatus(422);
        Event::assertNotDispatched(TherapyRoomSignal::class);
    }

    public function test_doctor_can_signal_within_their_own_live_room(): void
    {
        Event::fake();

        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->live()->create();
        $patient = User::factory()->create();
        app(TherapyParticipantAssigner::class)->handle($room, $patient);

        $response = $this->actingAs($doctor, 'doctor')->postJson(route('doctor.therapy-rooms.signal', $room), [
            'to' => 'patient-'.$patient->id,
            'type' => 'offer',
            'payload' => ['sdp' => 'fake-sdp', 'type' => 'offer'],
        ]);

        $response->assertOk();
        Event::assertDispatched(TherapyRoomSignal::class, fn (TherapyRoomSignal $event) => $event->from === 'doctor');
    }
}
