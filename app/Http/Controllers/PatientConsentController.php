<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientConsentRequest;
use App\Models\Doctor;
use App\Models\PatientConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientConsentController extends Controller
{
    /**
     * List every doctor the authenticated patient has been treated by, with their
     * current cross-provider access status.
     */
    public function index(): View
    {
        $patient = Auth::user();

        $treatingDoctors = Doctor::query()
            ->whereHas('appointments', fn ($query) => $query->where('user_id', $patient->id))
            ->with(['consentsReceived' => fn ($query) => $query->where('patient_id', $patient->id)])
            ->orderBy('name')
            ->get();

        return view('patient.consents.index', ['doctors' => $treatingDoctors]);
    }

    /**
     * Grant or revoke a doctor's access to the patient's cross-provider history.
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
