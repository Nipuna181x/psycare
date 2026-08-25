<?php

use App\Models\Doctor;
use App\Models\TherapyRoom;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence channel for a therapy room's WebRTC signaling. Shared by both the doctor guard
 * and the default (patient) guard, so both must be listed explicitly.
 *
 * The array returned here becomes the presence-channel member payload delivered to every
 * other participant's client (via `here`/`joining` events) — it must never contain a
 * patient's real name, only their anonymous label, since this is exactly how other
 * participants learn who's in the call.
 */
Broadcast::channel('therapy-room.{roomId}', function (Doctor|User $authUser, int $roomId) {
    $room = TherapyRoom::find($roomId);

    if (! $room) {
        return false;
    }

    if ($authUser instanceof Doctor) {
        if ($room->doctor_id !== $authUser->id) {
            return false;
        }

        return ['id' => 'doctor', 'label' => 'Doctor', 'role' => 'doctor'];
    }

    $participant = $room->activeParticipants()->where('patient_id', $authUser->id)->first();

    if (! $participant || ! $room->isJoinable()) {
        return false;
    }

    return ['id' => 'patient-'.$authUser->id, 'label' => $participant->anonymous_label, 'role' => 'patient'];
}, ['guards' => ['doctor', 'web']]);
