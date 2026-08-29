<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\LoginClinicStaffRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffAuthenticatedSessionController extends Controller
{
    /**
     * Display the clinic staff login view.
     */
    public function create(): View
    {
        return view('medical-center.auth.staff-login');
    }

    /**
     * Handle an incoming clinic staff authentication request.
     */
    public function store(LoginClinicStaffRequest $request): RedirectResponse
    {
        if (! Auth::guard('clinic_staff')->attempt($request->validated(), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $staff = Auth::guard('clinic_staff')->user();

        if ($staff->status !== 'active') {
            Auth::guard('clinic_staff')->logout();

            return back()->withErrors([
                'email' => 'Your staff access has been removed.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('medical-center.dashboard'));
    }
}
