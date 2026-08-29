<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorApprovalController extends Controller
{
    /**
     * Display doctors awaiting approval.
     */
    public function index(Request $request): View
    {
        $defaultStatus = $request->routeIs('admin.doctor-approvals.*') ? 'pending_approval' : 'all';
        $status = in_array($request->string('status')->toString(), ['all', 'pending_approval', 'approved', 'rejected', 'suspended'], true)
            ? $request->string('status')->toString()
            : $defaultStatus;
        $search = trim($request->string('search')->toString());

        $doctors = Doctor::query()
            ->withCount(['activeAffiliations', 'appointments'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $statusCounts = Doctor::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.doctors.index', compact('doctors', 'search', 'status', 'statusCounts'));
    }

    public function show(Doctor $doctor): View
    {
        $doctor->loadCount(['activeAffiliations', 'appointments', 'therapyRooms']);

        return view('admin.doctors.show', [
            'doctor' => $doctor,
            'clinics' => $doctor->clinics()->orderBy('name')->get(),
            'recentAppointments' => $doctor->appointments()
                ->with(['user:id,name', 'medicalCenter:id,name'])
                ->visibleToCareTeam()
                ->latest('appointment_date')
                ->latest('appointment_time')
                ->take(8)
                ->get(),
            'totalEarnings' => $doctor->payments()->succeeded()->sum('doctor_amount'),
        ]);
    }

    /**
     * Approve a doctor's registration.
     */
    public function approve(Doctor $doctor): RedirectResponse
    {
        if ($doctor->onboarding_step !== 'profile_complete') {
            return back()->withErrors(['approval' => 'This doctor must complete their profile before approval.']);
        }

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
