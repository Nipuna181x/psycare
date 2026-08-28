<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PatientHistoryVisibility
{
    /**
     * Decide what "Care History with Other Providers" a doctor may see for a patient.
     *
     * @return array{status: 'not_applicable'|'locked'|'unlocked_by_consent'|'emergency_override', appointments: Collection<int, Appointment>}
     */
    public function otherProvidersHistoryFor(User $patient, Doctor $doctor): array
    {
        $hasOtherDoctorHistory = $patient->appointments()->where('doctor_id', '!=', $doctor->id)->exists();

        if (! $hasOtherDoctorHistory) {
            return ['status' => 'not_applicable', 'appointments' => collect()];
        }

        $emergencyOverride = $this->patientHasCurrentEmergencyStatus($patient);
        $hasConsent = $emergencyOverride || $this->doctorHasActiveConsent($patient, $doctor);

        if (! $hasConsent) {
            return ['status' => 'locked', 'appointments' => collect()];
        }

        $appointments = $patient->appointments()
            ->where('doctor_id', '!=', $doctor->id)
            ->with(['doctor', 'medicalCenter', 'prescription.items'])
            ->orderByDesc('appointment_date')
            ->get();

        return [
            'status' => $emergencyOverride ? 'emergency_override' : 'unlocked_by_consent',
            'appointments' => $appointments,
        ];
    }

    /**
     * Whether the patient's most recent screener-completed appointment — across ALL
     * doctors, not just the current one — currently indicates crisis-level risk.
     * Mirrors DoctorCrisisQueue::forDoctor()'s detection exactly, generalized to the
     * whole patient rather than one doctor's slice of their history.
     */
    private function patientHasCurrentEmergencyStatus(User $patient): bool
    {
        return $patient->appointments()
            ->whereNotNull('screener_completed_at')
            ->latest('screener_completed_at')
            ->get()
            ->unique('user_id')
            ->first()
            ?->requiresCrisisEscalation() ?? false;
    }

    private function doctorHasActiveConsent(User $patient, Doctor $doctor): bool
    {
        return $patient->consents()->where('doctor_id', $doctor->id)->whereNull('revoked_at')->exists();
    }
}
