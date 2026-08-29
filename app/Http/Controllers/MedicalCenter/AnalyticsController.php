<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Services\CurrentClinic;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Display appointment volume, revenue, and busiest-doctor analytics
     * scoped to this clinic only.
     */
    public function index(CurrentClinic $currentClinic): View
    {
        $appointments = $currentClinic->model()->appointments()->visibleToCareTeam();

        $thisMonth = (clone $appointments)
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->count();

        $lastMonth = (clone $appointments)
            ->whereMonth('appointment_date', now()->subMonth()->month)
            ->whereYear('appointment_date', now()->subMonth()->year)
            ->count();

        $trendPct = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : null;

        $revenueThisMonth = (clone $appointments)
            ->where('status', 'completed')
            ->whereMonth('appointment_date', now()->month)
            ->whereYear('appointment_date', now()->year)
            ->sum('clinic_fee_charged');

        $busiestDoctors = (clone $appointments)
            ->selectRaw('doctor_id, count(*) as total')
            ->groupBy('doctor_id')
            ->orderByDesc('total')
            ->with('doctor')
            ->limit(6)
            ->get();

        $completedOrCancelled = (clone $appointments)->whereIn('status', ['completed', 'cancelled'])->count();
        $cancelled = (clone $appointments)->where('status', 'cancelled')->count();
        $cancellationRate = $completedOrCancelled > 0 ? round($cancelled / $completedOrCancelled * 100, 1) : null;

        $volumeTrend = collect(range(5, 0))->map(function (int $monthsAgo) use ($appointments) {
            $month = now()->subMonths($monthsAgo);

            return [
                'label' => $month->format('M'),
                'value' => (clone $appointments)
                    ->whereMonth('appointment_date', $month->month)
                    ->whereYear('appointment_date', $month->year)
                    ->count(),
            ];
        })->values()->all();

        return view('medical-center.analytics.index', [
            'thisMonth' => $thisMonth,
            'trendPct' => $trendPct,
            'revenueThisMonth' => $revenueThisMonth,
            'busiestDoctors' => $busiestDoctors,
            'cancellationRate' => $cancellationRate,
            'volumeTrend' => $volumeTrend,
        ]);
    }
}
