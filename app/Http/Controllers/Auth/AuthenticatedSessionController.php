<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginPatientRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the patient login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming patient authentication request.
     */
    public function store(LoginPatientRequest $request): RedirectResponse
    {
        if (! Auth::guard('web')->attempt($request->validated(), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if ($request->user()?->is_banned) {
            Auth::guard('web')->logout();

            return back()->withErrors([
                'email' => 'This account has been suspended. Please contact PsyCare support.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated patient session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
