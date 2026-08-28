<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StatusController extends Controller
{
    /**
     * Display the "application under review" message for a fully onboarded,
     * not-yet-approved doctor.
     */
    public function pending(): View
    {
        return view('doctor.status.pending');
    }

    /**
     * Display a blocked-access message for a rejected or suspended doctor.
     */
    public function blocked(): View
    {
        return view('doctor.status.blocked', [
            'reason' => Auth::guard('doctor')->user()->status,
        ]);
    }
}
