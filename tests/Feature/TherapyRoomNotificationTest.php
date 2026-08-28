<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\User;
use App\Notifications\TherapyRoomParticipantRemoved;
use App\Notifications\TherapyRoomScheduled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TherapyRoomNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_assigned_patient_is_notified_with_only_their_own_label(): void
    {
        Notification::fake();

        $doctor = Doctor::factory()->create();
        $patientA = User::factory()->create();
        $patientB = User::factory()->create();
        $bystander = User::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patientA->id]);
        Appointment::factory()->for($doctor)->create(['user_id' => $patientB->id]);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.store'), [
            'title' => 'Anxiety Support Circle',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 60,
            'patient_ids' => [$patientA->id, $patientB->id],
        ]);

        Notification::assertSentTo(
            $patientA,
            TherapyRoomScheduled::class,
            fn (TherapyRoomScheduled $notification) => $notification->participant->anonymous_label === 'Patient A'
        );

        Notification::assertSentTo(
            $patientB,
            TherapyRoomScheduled::class,
            fn (TherapyRoomScheduled $notification) => $notification->participant->anonymous_label === 'Patient B'
        );

        Notification::assertNotSentTo($bystander, TherapyRoomScheduled::class);
    }

    public function test_removed_participant_is_notified(): void
    {
        Notification::fake();

        $doctor = Doctor::factory()->create();
        $room = TherapyRoom::factory()->for($doctor)->create();
        $patient = User::factory()->create();
        Appointment::factory()->for($doctor)->create(['user_id' => $patient->id]);

        $this->actingAs($doctor, 'doctor')->post(route('doctor.therapy-rooms.participants.store', $room), [
            'patient_id' => $patient->id,
        ]);
        $participant = $room->participants()->firstOrFail();

        $this->actingAs($doctor, 'doctor')->delete(route('doctor.therapy-rooms.participants.destroy', [$room, $participant]));

        Notification::assertSentTo($patient, TherapyRoomParticipantRemoved::class);
    }
}
