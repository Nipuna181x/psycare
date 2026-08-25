<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\User;
use App\Services\TherapyParticipantAssigner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TherapyRoomChannelAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Channel authorization callbacks are only invoked by a real broadcaster driver — the
     * "null" driver used by default in tests is a no-op that always succeeds. Force the
     * "reverb" (Pusher-protocol) driver here so these tests actually exercise the callback
     * in routes/channels.php. This only computes an HMAC signature server-side; it doesn't
     * require a running Reverb server.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Switching the broadcaster after boot leaves the "reverb" driver instance with no
        // channel callbacks registered (routes/channels.php only ran against the driver that
        // was default at boot time), so re-run it now that "reverb" is selected.
        config(['broadcasting.default' => 'reverb']);
        require base_path('routes/channels.php');
    }

    public function test_room_owning_doctor_is_authorized(): void
    {
        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->live()->create();

        $response = $this->actingAs($doctor, 'doctor')->post('broadcasting/auth', [
            'channel_name' => 'presence-therapy-room.'.$room->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
    }

    public function test_assigned_patient_is_authorized(): void
    {
        $room = TherapyRoom::factory()->live()->create();
        $patient = User::factory()->create();
        app(TherapyParticipantAssigner::class)->handle($room, $patient);

        $response = $this->actingAs($patient, 'web')->post('broadcasting/auth', [
            'channel_name' => 'presence-therapy-room.'.$room->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertOk();
    }

    public function test_unassigned_patient_is_forbidden(): void
    {
        $room = TherapyRoom::factory()->live()->create();
        $patient = User::factory()->create();

        $response = $this->actingAs($patient, 'web')->post('broadcasting/auth', [
            'channel_name' => 'presence-therapy-room.'.$room->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertForbidden();
    }

    public function test_non_owning_doctor_is_forbidden(): void
    {
        $room = TherapyRoom::factory()->live()->create();
        $otherDoctor = Doctor::factory()->create();

        $response = $this->actingAs($otherDoctor, 'doctor')->post('broadcasting/auth', [
            'channel_name' => 'presence-therapy-room.'.$room->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_request_is_forbidden(): void
    {
        $room = TherapyRoom::factory()->live()->create();

        $response = $this->post('broadcasting/auth', [
            'channel_name' => 'presence-therapy-room.'.$room->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(403);
    }
}
