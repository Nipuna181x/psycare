<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Notifications\DoctorPortalNotification;
use App\Notifications\MedicalCenterPortalNotification;
use App\Services\DoctorClinicContext;
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
    public function index(DoctorClinicContext $clinicContext): View
    {
        $doctor = Auth::guard('doctor')->user();
        $clinicId = $clinicContext->current($doctor);

        $appointments = $doctor->appointments()
            ->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId))
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
    public function show(Appointment $appointment, DoctorClinicContext $clinicContext): View
    {
        $this->authorizeDoctorOwnsAppointment($appointment, $clinicContext);

        return view('doctor.appointments.show', [
            'appointment' => $appointment->load('user'),
        ]);
    }

    /**
     * Mark an appointment as completed or cancelled.
     */
    public function updateStatus(Request $request, Appointment $appointment, DoctorClinicContext $clinicContext): RedirectResponse
    {
        $this->authorizeDoctorOwnsAppointment($appointment, $clinicContext);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['completed', 'cancelled'])],
        ]);

        $appointment->update(['status' => $validated['status']]);

        if ($validated['status'] === 'cancelled') {
            Auth::guard('doctor')->user()->notify((new DoctorPortalNotification(
                type: 'appointment_cancelled',
                message: 'Appointment with '.$appointment->patient_name.' was cancelled.',
                link: route('doctor.appointments.show', $appointment, absolute: false),
            ))->afterCommit());

            $appointment->medicalCenter->notify((new MedicalCenterPortalNotification(
                type: 'appointment_cancelled',
                message: 'Appointment with '.$appointment->patient_name.' was cancelled by Dr. '.Auth::guard('doctor')->user()->name.'.',
                link: route('medical-center.appoinment-managment.show', $appointment, absolute: false),
            ))->afterCommit());
        }

        return back()->with('status', 'Appointment marked as '.$validated['status'].'.');
    }

    private function authorizeDoctorOwnsAppointment(Appointment $appointment, DoctorClinicContext $clinicContext): void
    {
        abort_unless($appointment->doctor_id === Auth::guard('doctor')->id(), 403);

        $clinicId = $clinicContext->current(Auth::guard('doctor')->user());
        abort_unless($clinicId === null || $appointment->medical_center_id === $clinicId, 403);
    }
}
