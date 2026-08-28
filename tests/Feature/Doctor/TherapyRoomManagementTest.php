<?php

namespace Tests\Feature\Doctor;

use App\Events\TherapyRoomEnded;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\TherapyRoomParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TherapyRoomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_sessions_index_renders_empty_states(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.therapy-rooms.index'));

        $response->assertOk()
            ->assertSee('No group sessions scheduled yet.')
            ->assertSee('No past group sessions yet.')
            ->assertSee('Schedule a room');
    }

    public function test_upcoming_room_is_clickable_and_detail_uses_only_anonymous_participant_label(): void
    {
        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->create([
            'title' => 'Anxiety Support Circle',
            'duration_minutes' => 60,
        ]);
        $patient = User::factory()->create(['name' => 'Private Patient Name']);
        TherapyRoomParticipant::factory()->for($room)->for($patient, 'patient')->create([
            'anonymous_label' => 'Patient A',
            'join_order' => 1,
        ]);

        $indexResponse = $this->actingAs($doctor, 'doctor')->get(route('doctor.therapy-rooms.index'));

        $indexResponse->assertOk()
            ->assertSee('Anxiety Support Circle')
            ->assertSee(route('doctor.therapy-rooms.show', $room), false)
            ->assertSee('60 min')
            ->assertSee('1 participant');

        $detailResponse = $this->actingAs($doctor, 'doctor')->get(route('doctor.therapy-rooms.show', $room));

        $detailResponse->assertOk()
            ->assertSee('Back to Group Sessions')
            ->assertSee('Patient A')
            ->assertDontSee('Private Patient Name')
            ->assertSee('Start session')
            ->assertSee('Edit details');
    }

    public function test_completed_room_detail_is_read_only_and_renders_notes_placeholder(): void
    {
        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->completed()->create([
            'title' => 'Completed Support Circle',
        ]);

        $indexResponse = $this->actingAs($doctor, 'doctor')->get(route('doctor.therapy-rooms.index'));

        $indexResponse->assertOk()
            ->assertSee('Completed Support Circle')
            ->assertSee('Completed');

        $detailResponse = $this->actingAs($doctor, 'doctor')->get(route('doctor.therapy-rooms.show', $room));

        $detailResponse->assertOk()
            ->assertSee('Session notes')
            ->assertSee('This session is read-only because it is completed.')
            ->assertDontSee('Start session');
    }

    public function test_doctor_can_create_room_and_labels_are_assigned_in_order(): void
    {
        $doctor = Doctor::factory()->create();
        $patientA = User::factory()->create();
        $patientB = User::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patientA->id, 'medical_center_id' => $doctor->medical_center_id]);
        Appointment::factory()->for($doctor)->create(['user_id' => $patientB->id, 'medical_center_id' => $doctor->medical_center_id]);

        $response = $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.store'), [
            'title' => 'Anxiety Support Circle',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'patient_ids' => [$patientA->id, $patientB->id],
        ]);

        $room = TherapyRoom::firstOrFail();
        $response->assertRedirect(route('doctor.therapy-rooms.show', $room));

        $this->assertDatabaseHas('therapy_room_participants', [
            'therapy_room_id' => $room->id,
            'patient_id' => $patientA->id,
            'anonymous_label' => 'Patient A',
        ]);
        $this->assertDatabaseHas('therapy_room_participants', [
            'therapy_room_id' => $room->id,
            'patient_id' => $patientB->id,
            'anonymous_label' => 'Patient B',
        ]);
    }

    public function test_doctor_cannot_add_a_patient_they_have_no_appointment_with(): void
    {
        $doctor = Doctor::factory()->create();
        $strangerPatient = User::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.store'), [
            'title' => 'Anxiety Support Circle',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'patient_ids' => [$strangerPatient->id],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('therapy_rooms', 0);
    }

    public function test_doctor_cannot_exceed_participant_cap(): void
    {
        $doctor = Doctor::factory()->create();
        $patientIds = [];

        for ($i = 0; $i < TherapyRoom::MAX_PARTICIPANTS + 1; $i++) {
            $patient = User::factory()->create();
            Appointment::factory()->for($doctor)->create(['user_id' => $patient->id, 'medical_center_id' => $doctor->medical_center_id]);
            $patientIds[] = $patient->id;
        }

        $response = $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.store'), [
            'title' => 'Anxiety Support Circle',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'patient_ids' => $patientIds,
        ]);

        $response->assertSessionHasErrors('patient_ids');
    }

    public function test_doctor_cannot_view_another_doctors_room(): void
    {
        $doctor = Doctor::factory()->create();
        $otherRoom = TherapyRoom::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->get(route('doctor.therapy-rooms.show', $otherRoom));

        $response->assertStatus(403);
    }

    public function test_doctor_cannot_start_another_doctors_room(): void
    {
        $doctor = Doctor::factory()->create();
        $otherRoom = TherapyRoom::factory()->create();

        $response = $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.start', $otherRoom));

        $response->assertStatus(403);
    }

    public function test_doctor_cannot_add_or_remove_participants_once_live(): void
    {
        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->live()->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id, 'medical_center_id' => $doctor->medical_center_id]);

        $response = $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.participants.store', $room), [
            'patient_id' => $patient->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_removing_a_participant_soft_removes_and_keeps_the_label(): void
    {
        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id, 'medical_center_id' => $doctor->medical_center_id]);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.participants.store', $room), [
            'patient_id' => $patient->id,
        ]);

        $participant = $room->participants()->firstOrFail();

        $response = $this->actingAs($doctor, 'doctor')->delete(route('doctor.therapy-rooms.participants.destroy', [$room, $participant]));

        $response->assertRedirect();
        $this->assertDatabaseHas('therapy_room_participants', [
            'id' => $participant->id,
            'anonymous_label' => $participant->anonymous_label,
        ]);
        $this->assertNotNull($participant->fresh()->removed_at);
        $this->assertEquals(0, $room->activeParticipants()->count());
    }

    public function test_starting_and_ending_a_room_updates_status(): void
    {
        Event::fake();

        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->create();

        $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.start', $room));
        $this->assertEquals('live', $room->fresh()->status);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.end', $room));
        $this->assertEquals('completed', $room->fresh()->status);

        Event::assertDispatched(TherapyRoomEnded::class);
    }
}
