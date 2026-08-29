<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarningsController extends Controller
{
    /**
     * Display the doctor's combined earnings across every clinic they work with.
     */
    public function index(): View
    {
        $doctor = Auth::guard('doctor')->user();

        $earningAppointments = $doctor->appointments()
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('doctor_fee_charged');

        $totalEarned = (clone $earningAppointments)->sum('doctor_fee_charged');

        $thisMonthEarned = (clone $earningAppointments)
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->sum('doctor_fee_charged');

        $breakdown = (clone $earningAppointments)
            ->with(['user', 'medicalCenter'])
            ->orderByDesc('appointment_date')
            ->get();

        $monthlyChart = collect(range(5, 0))->map(function (int $monthsAgo) use ($doctor): array {
            $month = now()->subMonths($monthsAgo);

            $total = $doctor->appointments()
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('doctor_fee_charged')
                ->whereMonth('appointment_date', $month->month)
                ->whereYear('appointment_date', $month->year)
                ->sum('doctor_fee_charged');

            return ['label' => $month->format('M'), 'value' => (float) $total];
        })->values()->all();

        $perClinic = $breakdown->groupBy('medical_center_id')->map(fn ($group) => [
            'clinic' => $group->first()->medicalCenter,
            'total' => $group->sum('doctor_fee_charged'),
        ])->values();

        return view('doctor.earnings.index', [
            'totalEarned' => $totalEarned,
            'thisMonthEarned' => $thisMonthEarned,
            'breakdown' => $breakdown,
            'monthlyChart' => $monthlyChart,
            'perClinic' => $perClinic,
        ]);
    }
}
