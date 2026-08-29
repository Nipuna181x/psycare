<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateContactRequest;
use App\Http\Requests\Doctor\UpdatePasswordRequest;
use App\Http\Requests\Doctor\UpdatePricingRequest;
use App\Http\Requests\Doctor\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('doctor.profile.edit', [
            'doctor' => Auth::guard('doctor')->user()->load('activeAffiliations.clinic'),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $doctor = Auth::guard('doctor')->user();
        $validated = $request->safe()->except('profile_photo');

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('doctor-avatars', 'public');
        }

        $doctor->update($validated);

        return back()->with('status', 'Profile information updated.');
    }

    public function updateContact(UpdateContactRequest $request): RedirectResponse
    {
        Auth::guard('doctor')->user()->update($request->validated());

        return back()->with('status', 'Contact information updated.');
    }

    public function updatePricing(UpdatePricingRequest $request): RedirectResponse
    {
        Auth::guard('doctor')->user()->update($request->validated());

        return back()->with('status', 'Pricing updated.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        Auth::guard('doctor')->user()->update(['password' => $request->validated('password')]);

        return back()->with('status', 'Password updated successfully.');
    }
}
