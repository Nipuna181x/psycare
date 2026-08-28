<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\DoctorClinicContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicContextController extends Controller
{
    /**
     * Switch the active clinic context for the authenticated doctor's session.
     */
    public function update(Request $request, DoctorClinicContext $context): RedirectResponse
    {
        $validated = $request->validate([
            'clinic_id' => ['nullable', 'integer'],
        ]);

        $context->set(Auth::guard('doctor')->user(), $validated['clinic_id'] ?? null);

        return back();
    }
}
