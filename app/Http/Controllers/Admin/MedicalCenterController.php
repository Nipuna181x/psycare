<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalCenterController extends Controller
{
    /**
     * Display all medical center registrations.
     */
    public function index(): View
    {
        $medicalCenters = MedicalCenter::latest()->paginate(15);

        return view('admin.user-managment.index', compact('medicalCenters'));
    }

    /**
     * Approve a medical center registration.
     */
    public function approve(MedicalCenter $medicalCenter): RedirectResponse
    {
        $medicalCenter->update(['status' => 'approved']);

        return back()->with('status', "{$medicalCenter->name} has been approved.");
    }

    /**
     * Reject a medical center registration.
     */
    public function reject(MedicalCenter $medicalCenter): RedirectResponse
    {
        $medicalCenter->update(['status' => 'rejected']);

        return back()->with('status', "{$medicalCenter->name} has been rejected.");
    }
}
