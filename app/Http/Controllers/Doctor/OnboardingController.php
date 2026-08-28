<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateDoctorOnboardingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * Display the "complete your profile" step.
     */
    public function edit(): View
    {
        return view('doctor.onboarding.edit', [
            'doctor' => Auth::guard('doctor')->user(),
        ]);
    }

    /**
     * Save the doctor's profile details and mark onboarding complete.
     */
    public function update(UpdateDoctorOnboardingRequest $request): RedirectResponse
    {
        $doctor = Auth::guard('doctor')->user();
        $validated = $request->safe()->except('profile_photo');

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('doctor-avatars', 'public');
        }

        $validated['onboarding_step'] = 'profile_complete';

        $doctor->update($validated);

        return redirect()->route('doctor.dashboard');
    }
}
