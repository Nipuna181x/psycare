<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\UpdateContactRequest;
use App\Http\Requests\MedicalCenter\UpdateHoursRequest;
use App\Http\Requests\MedicalCenter\UpdateLogoRequest;
use App\Http\Requests\MedicalCenter\UpdatePricingRequest;
use App\Http\Requests\MedicalCenter\UpdateProfileRequest;
use App\Services\CurrentClinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the clinic's settings page.
     */
    public function edit(CurrentClinic $currentClinic): View
    {
        return view('medical-center.settings.edit', [
            'clinic' => $currentClinic->model(),
        ]);
    }

    /**
     * Update the clinic's name and public description.
     */
    public function updateProfile(UpdateProfileRequest $request, CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->update($request->validated());

        return back()->with('status', 'Profile updated.');
    }

    /**
     * Update the clinic's phone and address.
     */
    public function updateContact(UpdateContactRequest $request, CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->update($request->validated());

        return back()->with('status', 'Contact information updated.');
    }

    /**
     * Update the clinic's logo.
     */
    public function updateLogo(UpdateLogoRequest $request, CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->update([
            'logo_path' => $request->file('logo')->store('clinic-logos', 'public'),
        ]);

        return back()->with('status', 'Logo updated.');
    }

    /**
     * Update the clinic's operating hours.
     */
    public function updateHours(UpdateHoursRequest $request, CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->update([
            'operating_hours' => $request->validated('hours'),
        ]);

        return back()->with('status', 'Operating hours updated.');
    }

    /**
     * Update the clinic's flat facility fee.
     */
    public function updatePricing(UpdatePricingRequest $request, CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->update($request->validated());

        return back()->with('status', 'Pricing updated.');
    }
}
