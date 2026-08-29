<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Collection;

class DoctorCrisisQueue
{
    /** @return Collection<int, Appointment> */
    public function forDoctor(Doctor $doctor, ?int $clinicId = null): Collection
    {
        return $doctor->appointments()
            ->visibleToCareTeam()
            ->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))
            ->whereNotNull('screener_completed_at')
            ->with('user')
            ->latest('screener_completed_at')
            ->get()
            ->unique('user_id')
            ->filter(fn (Appointment $appointment): bool => $appointment->requiresCrisisEscalation())
            ->values();
    }

    public function unreviewedCount(Doctor $doctor, ?int $clinicId = null): int
    {
        return $this->forDoctor($doctor, $clinicId)->where('escalation_reviewed', false)->count();
    }
}
