<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\UpdatePricingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the clinic's settings page.
     */
    public function edit(): View
    {
        return view('medical-center.settings.edit', [
            'clinic' => Auth::guard('medical_center')->user(),
        ]);
    }

    /**
     * Update the clinic's flat facility fee.
     */
    public function updatePricing(UpdatePricingRequest $request): RedirectResponse
    {
        Auth::guard('medical_center')->user()->update($request->validated());

        return back()->with('status', 'Pricing updated.');
    }
}
