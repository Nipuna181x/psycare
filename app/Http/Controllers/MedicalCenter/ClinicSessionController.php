<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicSessionController extends Controller
{
    /**
     * Destroy the currently authenticated clinic session, regardless of
     * whether it was authenticated as the primary medical_center login or a
     * clinic_staff seat.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('medical_center')->logout();
        Auth::guard('clinic_staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('medical-center.login');
    }
}
