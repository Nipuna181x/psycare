<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the doctor's dashboard.
     */
    public function index(): View
    {
        $doctor = Auth::guard('doctor')->user()->load('medicalCenter');

        return view('doctor.dashboard', [
            'doctor' => $doctor,
        ]);
    }
}
