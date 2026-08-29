<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientConsentRequest;
use App\Models\Doctor;
use App\Models\PatientConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PatientConsentController extends Controller
{
    /**
     * Grant or revoke a doctor's access to the patient's cross-provider history.
     * The list of doctors and their current status is rendered from the Settings
     * page (see PatientProfileController::edit()); this controller only handles
     * the toggle action itself.
     */
    public function update(PatientConsentRequest $request, Doctor $doctor): RedirectResponse
    {
        $patient = Auth::user();
        $grant = $request->boolean('grant');

        if ($grant) {
            PatientConsent::query()->updateOrCreate(
                ['patient_id' => $patient->id, 'doctor_id' => $doctor->id],
                ['granted_at' => now(), 'revoked_at' => null],
            );
        } else {
            PatientConsent::query()
                ->where('patient_id', $patient->id)
                ->where('doctor_id', $doctor->id)
                ->update(['revoked_at' => now()]);
        }

        return back()->with('status', $grant ? 'Access granted.' : 'Access revoked.');
    }
}
