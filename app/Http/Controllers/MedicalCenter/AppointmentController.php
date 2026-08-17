<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Display every appointment booked across the clinic's doctors.
     */
    public function index(): View
    {
        $medicalCenter = Auth::guard('medical_center')->user();

        $appointments = $medicalCenter->appointments()
            ->with(['doctor', 'user'])
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(15);

        return view('medical-center.appoinment-managment.index', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Display a single appointment booked at this clinic.
     */
    public function show(Appointment $appointment): View
    {
        abort_unless($appointment->medical_center_id === Auth::guard('medical_center')->id(), 403);

        return view('medical-center.appoinment-managment.show', [
            'appointment' => $appointment->load(['doctor', 'user']),
        ]);
    }
}
