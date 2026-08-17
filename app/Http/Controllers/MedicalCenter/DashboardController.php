<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the medical centre's dashboard.
     */
    public function index(): View
    {
        $medicalCenter = Auth::guard('medical_center')->user();

        $doctors = $medicalCenter->doctors();

        $statusCounts = [
            'active' => (clone $doctors)->where('status', 'active')->count(),
            'inactive' => (clone $doctors)->where('status', 'inactive')->count(),
        ];

        $specializations = (clone $doctors)
            ->selectRaw("COALESCE(NULLIF(specialization, ''), 'Unspecified') as specialization, COUNT(*) as total")
            ->groupBy('specialization')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $appointments = $medicalCenter->appointments();

        return view('medical-center.dashboard', [
            'totalDoctors' => array_sum($statusCounts),
            'activeDoctors' => $statusCounts['active'],
            'inactiveDoctors' => $statusCounts['inactive'],
            'specializationCount' => $specializations->count(),
            'statusCounts' => $statusCounts,
            'specializations' => $specializations,
            'recentDoctors' => (clone $doctors)->latest()->take(5)->get(),
            'todayAppointments' => (clone $appointments)->where('status', 'confirmed')->today()->count(),
            'upcomingAppointments' => (clone $appointments)->upcoming()->count(),
            'completedAppointments' => (clone $appointments)->where('status', 'completed')->count(),
            'recentAppointments' => (clone $appointments)->with(['doctor', 'user'])->latest('appointment_date')->take(5)->get(),
        ]);
    }
}
