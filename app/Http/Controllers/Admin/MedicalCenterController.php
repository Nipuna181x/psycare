<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalCenterController extends Controller
{
    /**
     * Display all medical center registrations.
     */
    public function index(Request $request): View
    {
        $status = in_array($request->string('status')->toString(), ['all', 'pending', 'approved', 'rejected'], true)
            ? $request->string('status')->toString()
            : 'all';
        $search = trim($request->string('search')->toString());

        $medicalCenters = MedicalCenter::query()
            ->withCount(['affiliatedDoctors', 'appointments'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $statusCounts = MedicalCenter::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.medical-centers.index', compact('medicalCenters', 'search', 'status', 'statusCounts'));
    }

    public function show(MedicalCenter $medicalCenter): View
    {
        $medicalCenter->loadCount(['affiliatedDoctors', 'appointments', 'staff']);

        return view('admin.medical-centers.show', [
            'medicalCenter' => $medicalCenter,
            'doctors' => $medicalCenter->affiliatedDoctors()->orderBy('name')->get(),
            'recentAppointments' => $medicalCenter->appointments()
                ->with(['doctor:id,name', 'user:id,name'])
                ->visibleToCareTeam()
                ->latest('appointment_date')
                ->latest('appointment_time')
                ->take(8)
                ->get(),
            'totalRevenue' => $medicalCenter->payments()->succeeded()->sum('amount'),
        ]);
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
