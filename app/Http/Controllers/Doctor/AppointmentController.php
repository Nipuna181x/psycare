<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Display the authenticated doctor's appointments.
     */
    public function index(): View
    {
        $doctor = Auth::guard('doctor')->user();

        $appointments = $doctor->appointments()
            ->with('user')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return view('doctor.appointments.index', [
            'today' => $appointments->where('status', 'confirmed')->filter(fn (Appointment $appointment) => $appointment->appointment_date->isToday()),
            'upcoming' => $appointments->where('status', 'confirmed')->filter(fn (Appointment $appointment) => $appointment->appointment_date->isFuture()),
            'history' => $appointments->whereIn('status', ['completed', 'cancelled']),
        ]);
    }

    /**
     * Display a single appointment, including the full pre-assessment.
     */
    public function show(Appointment $appointment): View
    {
        $this->authorizeDoctorOwnsAppointment($appointment);

        return view('doctor.appointments.show', [
            'appointment' => $appointment->load('user'),
        ]);
    }

    /**
     * Mark an appointment as completed or cancelled.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeDoctorOwnsAppointment($appointment);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['completed', 'cancelled'])],
        ]);

        $appointment->update(['status' => $validated['status']]);

        return back()->with('status', 'Appointment marked as '.$validated['status'].'.');
    }

    private function authorizeDoctorOwnsAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === Auth::guard('doctor')->id(), 403);
    }
}
