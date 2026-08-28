<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\LoginDoctorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the doctor login view.
     */
    public function create(): View
    {
        return view('doctor.auth.login');
    }

    /**
     * Handle an incoming doctor authentication request.
     */
    public function store(LoginDoctorRequest $request): RedirectResponse
    {
        if (! Auth::guard('doctor')->attempt($request->validated(), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('doctor.dashboard'));
    }

    /**
     * Destroy an authenticated doctor session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('doctor')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('doctor.login');
    }
}
