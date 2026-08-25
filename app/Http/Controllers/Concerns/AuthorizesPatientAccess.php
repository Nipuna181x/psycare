<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait AuthorizesPatientAccess
{
    /**
     * Ensure only a doctor treating this patient, or an admin, can access the patient's records.
     * Returns the guard name so the caller can render the matching panel view.
     */
    private function authorizeAccess(User $patient): string
    {
        if ($doctor = Auth::guard('doctor')->user()) {
            abort_unless(
                Appointment::where('doctor_id', $doctor->id)->where('user_id', $patient->id)->exists(),
                403
            );

            return 'doctor';
        }

        abort_unless(Auth::guard('admin')->check(), 403);

        return 'admin';
    }
}
