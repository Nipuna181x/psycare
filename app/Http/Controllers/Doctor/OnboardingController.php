<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateDoctorOnboardingRequest;
use App\Models\Admin;
use App\Notifications\AdminApprovalRequested;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
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
        $wasProfileComplete = $doctor->onboarding_step === 'profile_complete';
        $validated = $request->safe()->except('profile_photo');

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('doctor-avatars', 'public');
        }

        $validated['onboarding_step'] = 'profile_complete';

        $doctor->update($validated);

        if (! $wasProfileComplete) {
            Notification::send(Admin::all(), new AdminApprovalRequested(
                type: 'doctor_application',
                message: "Dr. {$doctor->name} completed an application and is ready for approval.",
                link: route('admin.doctors.show', $doctor, absolute: false),
                subjectId: $doctor->id,
            ));
        }

        return redirect()->route('doctor.dashboard');
    }
}
