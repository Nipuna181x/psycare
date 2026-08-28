<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePatientPasswordRequest;
use App\Http\Requests\UpdatePatientProfileRequest;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientProfileController extends Controller
{
    /**
     * Display the patient's settings page: profile, password, and care access.
     */
    public function edit(): View
    {
        $patient = Auth::user();

        $treatingDoctors = Doctor::query()
            ->whereHas('appointments', fn ($query) => $query->where('user_id', $patient->id))
            ->with(['consentsReceived' => fn ($query) => $query->where('patient_id', $patient->id)])
            ->orderBy('name')
            ->get();

        return view('patient.settings.edit', [
            'patient' => $patient,
            'doctors' => $treatingDoctors,
        ]);
    }

    /**
     * Update the patient's basic profile information.
     */
    public function updateProfile(UpdatePatientProfileRequest $request): RedirectResponse
    {
        Auth::user()->update($request->validated());

        return back()->with('status', 'Profile updated.');
    }

    /**
     * Update the patient's password.
     */
    public function updatePassword(UpdatePatientPasswordRequest $request): RedirectResponse
    {
        Auth::user()->update(['password' => $request->validated('password')]);

        return back()->with('status', 'Password updated successfully.');
    }
}
