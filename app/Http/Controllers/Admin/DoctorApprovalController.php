<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorApprovalController extends Controller
{
    /**
     * Display doctors awaiting approval.
     */
    public function index(): View
    {
        $doctors = Doctor::query()
            ->where('status', 'pending_approval')
            ->where('onboarding_step', 'profile_complete')
            ->latest()
            ->paginate(15);

        return view('admin.doctor-approvals.index', compact('doctors'));
    }

    /**
     * Approve a doctor's registration.
     */
    public function approve(Doctor $doctor): RedirectResponse
    {
        $doctor->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::guard('admin')->id(),
        ]);

        return back()->with('status', "{$doctor->name} has been approved.");
    }

    /**
     * Reject a doctor's registration.
     */
    public function reject(Doctor $doctor): RedirectResponse
    {
        $doctor->update(['status' => 'rejected']);

        return back()->with('status', "{$doctor->name} has been rejected.");
    }
}
