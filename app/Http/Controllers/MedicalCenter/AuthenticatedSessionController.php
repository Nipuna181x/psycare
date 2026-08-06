<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\LoginMedicalCenterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the medical center login view.
     */
    public function create(): View
    {
        return view('medical-center.auth.login');
    }

    /**
     * Handle an incoming medical center authentication request.
     */
    public function store(LoginMedicalCenterRequest $request): RedirectResponse
    {
        if (! Auth::guard('medical_center')->attempt($request->validated(), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $medicalCenter = Auth::guard('medical_center')->user();

        if ($medicalCenter->status !== 'approved') {
            Auth::guard('medical_center')->logout();

            return back()->withErrors([
                'email' => match ($medicalCenter->status) {
                    'rejected' => 'Your medical center registration was rejected. Please contact support.',
                    default => 'Your medical center registration is pending admin approval.',
                },
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('medical-center.dashboard'));
    }

    /**
     * Destroy an authenticated medical center session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('medical_center')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('medical-center.login');
    }
}
