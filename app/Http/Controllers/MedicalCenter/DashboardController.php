<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Services\CurrentClinic;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the medical centre's dashboard.
     */
    public function index(CurrentClinic $currentClinic): View
    {
        $medicalCenter = $currentClinic->model();

        $affiliations = $medicalCenter->affiliations();

        $statusCounts = [
            'active' => (clone $affiliations)->where('status', 'active')->count(),
            'requested' => (clone $affiliations)->where('status', 'requested')->count(),
        ];

        $specializations = (clone $affiliations)
            ->where('doctor_clinic_affiliations.status', 'active')
            ->join('doctors', 'doctors.id', '=', 'doctor_clinic_affiliations.doctor_id')
            ->selectRaw("COALESCE(NULLIF(doctors.specialization, ''), 'Unspecified') as specialization, COUNT(*) as total")
            ->groupBy('specialization')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $appointments = $medicalCenter->appointments();

        return view('medical-center.dashboard', [
            'totalDoctors' => array_sum($statusCounts),
            'activeDoctors' => $statusCounts['active'],
            'requestedDoctors' => $statusCounts['requested'],
            'specializationCount' => $specializations->count(),
            'statusCounts' => $statusCounts,
            'specializations' => $specializations,
            'recentDoctors' => (clone $affiliations)->where('status', 'active')->with('doctor')->latest()->take(5)->get(),
            'todayAppointments' => (clone $appointments)->where('status', 'confirmed')->today()->count(),
            'upcomingAppointments' => (clone $appointments)->upcoming()->count(),
            'completedAppointments' => (clone $appointments)->where('status', 'completed')->count(),
            'recentAppointments' => (clone $appointments)->with(['doctor', 'user'])->latest('appointment_date')->take(5)->get(),
        ]);
    }
}
