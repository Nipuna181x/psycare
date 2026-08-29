<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DoctorEarningsCalculator
{
    /**
     * Base query for a doctor's fee-counted appointments — matches the
     * Earnings page's definition exactly: confirmed or completed, with a fee snapshot.
     *
     * @return Builder<Appointment>
     */
    public function qualifyingAppointments(Doctor $doctor): Builder
    {
        return $doctor->appointments()
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNotNull('doctor_fee_charged');
    }

    public function earnedInMonth(Doctor $doctor, Carbon $month): float
    {
        return (float) (clone $this->qualifyingAppointments($doctor))
            ->whereMonth('appointment_date', $month->month)
            ->whereYear('appointment_date', $month->year)
            ->sum('doctor_fee_charged');
    }

    /**
     * Per-clinic totals and appointment counts for a doctor, for the given month.
     *
     * @return Collection<int, array{clinic: MedicalCenter, total: float, count: int}>
     */
    public function perClinicBreakdownForMonth(Doctor $doctor, Carbon $month): Collection
    {
        return (clone $this->qualifyingAppointments($doctor))
            ->whereMonth('appointment_date', $month->month)
            ->whereYear('appointment_date', $month->year)
            ->with('medicalCenter')
            ->get()
            ->groupBy('medical_center_id')
            ->map(fn ($group) => [
                'clinic' => $group->first()->medicalCenter,
                'total' => (float) $group->sum('doctor_fee_charged'),
                'count' => $group->count(),
            ])
            ->values();
    }
}
