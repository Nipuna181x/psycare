<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the platform-wide admin dashboard.
     */
    public function index(): View
    {
        $statusCounts = [
            'approved' => MedicalCenter::where('status', 'approved')->count(),
            'pending' => MedicalCenter::where('status', 'pending')->count(),
            'rejected' => MedicalCenter::where('status', 'rejected')->count(),
        ];

        $specializations = Doctor::query()
            ->selectRaw("COALESCE(NULLIF(specialization, ''), 'Unspecified') as specialization, COUNT(*) as total")
            ->groupBy('specialization')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'totalMedicalCenters' => array_sum($statusCounts),
            'pendingApprovalsCount' => $statusCounts['pending'],
            'totalDoctors' => Doctor::count(),
            'totalPatients' => User::count(),
            'statusCounts' => $statusCounts,
            'specializations' => $specializations,
            'pendingCenters' => MedicalCenter::where('status', 'pending')->latest()->take(5)->get(),
            'recentCenters' => MedicalCenter::latest()->take(5)->get(),
        ]);
    }
}
