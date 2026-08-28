<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Display the authenticated patient's own appointments.
     */
    public function index(): View
    {
        $appointments = Auth::user()->appointments()
            ->with(['doctor', 'medicalCenter'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->get();

        return view('appointments.index', [
            'upcoming' => $appointments->where('status', 'confirmed'),
            'past' => $appointments->whereIn('status', ['completed', 'cancelled']),
        ]);
    }
}
