<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\User;

class TherapyRoomPolicy
{
    /**
     * The doctor who owns the room may always view it; a patient may only view it
     * while they are an active (non-removed) participant.
     */
    public function view(Doctor|User $viewer, TherapyRoom $room): bool
    {
        if ($viewer instanceof Doctor) {
            return $room->doctor_id === $viewer->id;
        }

        return $room->activeParticipants()->where('patient_id', $viewer->id)->exists();
    }

    public function update(Doctor $doctor, TherapyRoom $room): bool
    {
        return $room->doctor_id === $doctor->id;
    }

    /**
     * Patients may only be added to or removed from a room before it goes live.
     */
    public function manageParticipants(Doctor $doctor, TherapyRoom $room): bool
    {
        return $room->doctor_id === $doctor->id && $room->isEditable();
    }

    public function start(Doctor $doctor, TherapyRoom $room): bool
    {
        return $room->doctor_id === $doctor->id && $room->status === 'scheduled';
    }

    public function end(Doctor $doctor, TherapyRoom $room): bool
    {
        return $room->doctor_id === $doctor->id && $room->status === 'live';
    }

    /**
     * Only patients explicitly assigned to the room may join it or receive its signaling.
     */
    public function join(User $patient, TherapyRoom $room): bool
    {
        return $room->isJoinable()
            && $room->activeParticipants()->where('patient_id', $patient->id)->exists();
    }
}
