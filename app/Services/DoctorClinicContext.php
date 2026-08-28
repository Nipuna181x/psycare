<?php

namespace App\Services;

use App\Models\Doctor;

class DoctorClinicContext
{
    private const SESSION_KEY = 'doctor_active_clinic_id';

    /**
     * The clinic id currently in scope for this doctor's session, or null when
     * no specific clinic is selected (either they have zero active affiliations,
     * or they have 2+ and have chosen "All clinics").
     */
    public function current(Doctor $doctor): ?int
    {
        $activeClinicIds = $doctor->activeAffiliations()->pluck('clinic_id');

        if ($activeClinicIds->count() <= 1) {
            return $activeClinicIds->first();
        }

        $stored = session(self::SESSION_KEY);

        return $activeClinicIds->contains($stored) ? $stored : null;
    }

    /**
     * Set the active clinic context for this doctor's session. Pass null to
     * select "All clinics".
     */
    public function set(Doctor $doctor, ?int $clinicId): void
    {
        if ($clinicId !== null) {
            abort_unless($doctor->activeAffiliations()->where('clinic_id', $clinicId)->exists(), 403);
        }

        session([self::SESSION_KEY => $clinicId]);
    }

    /**
     * Whether the clinic switcher UI should be shown at all — only relevant
     * once a doctor has more than one active affiliation.
     */
    public function requiresSwitcher(Doctor $doctor): bool
    {
        return $doctor->activeAffiliations()->count() > 1;
    }
}
