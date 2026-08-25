<?php

namespace App\Services;

use App\Models\TherapyRoom;
use App\Models\TherapyRoomParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TherapyParticipantAssigner
{
    /**
     * Add a patient to a room, assigning the next anonymous label in join order.
     * Labels are persisted permanently and never regenerated or reused.
     */
    public function handle(TherapyRoom $room, User $patient): TherapyRoomParticipant
    {
        return DB::transaction(function () use ($room, $patient): TherapyRoomParticipant {
            $nextOrder = (int) $room->participants()->max('join_order') + 1;

            return $room->participants()->create([
                'patient_id' => $patient->id,
                'anonymous_label' => 'Patient '.$this->letterFor($nextOrder),
                'join_order' => $nextOrder,
            ]);
        });
    }

    /**
     * Convert a 1-based join order into a spreadsheet-style label suffix (1=A, 2=B, ..., 26=Z, 27=AA, ...).
     */
    private function letterFor(int $order): string
    {
        $label = '';

        while ($order > 0) {
            $order--;
            $label = chr(65 + ($order % 26)).$label;
            $order = intdiv($order, 26);
        }

        return $label;
    }
}
