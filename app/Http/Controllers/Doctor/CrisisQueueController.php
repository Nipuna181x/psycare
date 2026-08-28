<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\DoctorClinicContext;
use App\Services\DoctorCrisisQueue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CrisisQueueController extends Controller
{
    public function index(Request $request, DoctorCrisisQueue $crisisQueue, DoctorClinicContext $clinicContext): View
    {
        $doctor = Auth::guard('doctor')->user();
        $sort = $request->string('sort')->toString() === 'overdue' ? 'overdue' : 'recent';
        $appointments = $crisisQueue->forDoctor($doctor, $clinicContext->current($doctor));
        $unreviewed = $appointments->where('escalation_reviewed', false);

        $unreviewed = $sort === 'overdue'
            ? $unreviewed->sortBy(fn (Appointment $appointment): string => $appointment->appointment_date->format('Y-m-d').' '.$appointment->appointment_time)
            : $unreviewed->sortByDesc('screener_completed_at');

        return view('doctor.crisis-queue.index', [
            'unreviewed' => $unreviewed,
            'reviewed' => $appointments->where('escalation_reviewed', true)->sortByDesc('escalation_reviewed_at'),
            'sort' => $sort,
        ]);
    }

    public function acknowledge(Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->doctor_id === Auth::guard('doctor')->id(), 403);
        abort_unless($appointment->requiresCrisisEscalation(), 422);

        $appointment->update([
            'escalation_reviewed' => true,
            'escalation_reviewed_at' => now(),
        ]);

        return back()->with('status', 'Escalation acknowledged and moved to reviewed.');
    }
}
