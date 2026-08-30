<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Services\DoctorClinicContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the doctor's dashboard.
     */
    public function index(DoctorClinicContext $clinicContext): View
    {
        $doctor = Auth::guard('doctor')->user()->load('activeAffiliations.clinic');
        $clinicId = $clinicContext->current($doctor);

        $appointments = $doctor->appointments()->when($clinicId, fn ($query) => $query->where('medical_center_id', $clinicId));

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
            'clinicId' => $clinicId,
            'noClinicAffiliation' => $doctor->activeAffiliations->isEmpty(),
            'noPriceSet' => ! $doctor->isPriced(),
            'todayCount' => $todayCount,
            'upcomingCount' => $upcomingCount,
            'completedCount' => $completedCount,
            'riskCounts' => $riskCounts,
            'nextAppointments' => (clone $appointments)->upcoming()->with('user')->orderBy('appointment_date')->orderBy('appointment_time')->take(5)->get(),
        ]);
    }
}
