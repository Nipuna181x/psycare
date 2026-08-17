<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the doctor's dashboard.
     */
    public function index(): View
    {
        $doctor = Auth::guard('doctor')->user()->load('medicalCenter');

        $appointments = $doctor->appointments();

        $todayCount = (clone $appointments)->where('status', 'confirmed')->today()->count();
        $upcomingCount = (clone $appointments)->upcoming()->count();
        $completedCount = (clone $appointments)->where('status', 'completed')->count();

        $riskCounts = (clone $appointments)
            ->where('status', 'confirmed')
            ->selectRaw("COALESCE(pre_assessment_risk_level, 'unrated') as risk, COUNT(*) as total")
            ->groupBy('risk')
            ->pluck('total', 'risk');

        return view('doctor.dashboard', [
            'doctor' => $doctor,
            'todayCount' => $todayCount,
            'upcomingCount' => $upcomingCount,
            'completedCount' => $completedCount,
            'riskCounts' => $riskCounts,
            'nextAppointments' => (clone $appointments)->upcoming()->with('user')->orderBy('appointment_date')->orderBy('appointment_time')->take(5)->get(),
        ]);
    }
}
