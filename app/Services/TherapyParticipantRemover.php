<?php

namespace App\Services;

use App\Models\TherapyRoomParticipant;

class TherapyParticipantRemover
{
    /**
     * Soft-remove a participant from a room. The pivot row and its anonymous label are kept
     * (not deleted) so the label is never reused and the assignment history stays intact.
     */
    public function handle(TherapyRoomParticipant $participant): void
    {
        $participant->update(['removed_at' => now()]);
    }
}
