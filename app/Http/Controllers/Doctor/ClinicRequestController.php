<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorClinicAffiliation;
use App\Notifications\ClinicRequestResponded;
use App\Notifications\MedicalCenterPortalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClinicRequestController extends Controller
{
    /**
     * List clinic work requests for the authenticated doctor, grouped by status.
     */
    public function index(): View
    {
        $doctor = Auth::guard('doctor')->user();
        $requests = $doctor->affiliations()->with('clinic')->latest()->get();

        return view('doctor.clinic-requests.index', [
            'pending' => $requests->where('status', 'requested')->values(),
            'active' => $requests->where('status', 'active')->values(),
            'history' => $requests->whereIn('status', ['declined', 'ended'])->values(),
        ]);
    }

    /**
     * Accept a clinic's work request, making the affiliation active.
     */
    public function accept(DoctorClinicAffiliation $affiliation): RedirectResponse
    {
        $this->authorizeOwnership($affiliation);

        $affiliation->update([
            'status' => 'active',
            'responded_by_doctor_at' => now(),
        ]);

        $affiliation->clinic->notify((new MedicalCenterPortalNotification(
            type: 'doctor_accepted',
            message: 'Dr. '.$affiliation->doctor->name.' accepted your work request.',
            link: route('medical-center.doctors.index', ['tab' => 'my-doctors'], absolute: false),
        ))->afterCommit());

        $affiliation->clinic->notify((new ClinicRequestResponded($affiliation, accepted: true))->afterCommit());

        return back()->with('status', "You are now affiliated with {$affiliation->clinic->name}.");
    }

    /**
     * Decline a clinic's work request.
     */
    public function decline(DoctorClinicAffiliation $affiliation): RedirectResponse
    {
        $this->authorizeOwnership($affiliation);

        $affiliation->update([
            'status' => 'declined',
            'responded_by_doctor_at' => now(),
        ]);

        $affiliation->clinic->notify((new MedicalCenterPortalNotification(
            type: 'doctor_declined',
            message: 'Dr. '.$affiliation->doctor->name.' declined your work request.',
            link: route('medical-center.doctors.index', ['tab' => 'pending'], absolute: false),
        ))->afterCommit());

        $affiliation->clinic->notify((new ClinicRequestResponded($affiliation, accepted: false))->afterCommit());

        return back()->with('status', 'Request declined.');
    }

    private function authorizeOwnership(DoctorClinicAffiliation $affiliation): void
    {
        abort_unless($affiliation->doctor_id === Auth::guard('doctor')->id(), 403);
    }
}
