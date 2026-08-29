<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $periodStart = now()->startOfMonth()->subMonths(5);
        $months = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo));

        $appointmentsInPeriod = Appointment::query()->where('created_at', '>=', $periodStart)->get(['created_at']);
        $patientsInPeriod = User::query()->where('created_at', '>=', $periodStart)->get(['created_at']);
        $doctorsInPeriod = Doctor::query()->where('created_at', '>=', $periodStart)->get(['created_at']);
        $centersInPeriod = MedicalCenter::query()->where('created_at', '>=', $periodStart)->get(['created_at']);

        $appointmentStatuses = Appointment::query()
            ->visibleToCareTeam()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $riskCounts = Appointment::query()
            ->whereNotNull('pre_assessment_risk_level')
            ->selectRaw('pre_assessment_risk_level, count(*) as total')
            ->groupBy('pre_assessment_risk_level')
            ->pluck('total', 'pre_assessment_risk_level');

        return view('admin.reports.index', [
            'totalPatients' => User::count(),
            'approvedDoctors' => Doctor::where('status', 'approved')->count(),
            'approvedCenters' => MedicalCenter::where('status', 'approved')->count(),
            'succeededRevenue' => Payment::succeeded()->sum('amount'),
            'revenueThisMonth' => Payment::succeeded()
                ->whereBetween('processed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'appointmentStatuses' => $appointmentStatuses,
            'riskCounts' => $riskCounts,
            'appointmentTrend' => $this->monthlyTrend($months, $appointmentsInPeriod),
            'registrationTrend' => [
                'patients' => $this->monthlyTrend($months, $patientsInPeriod),
                'doctors' => $this->monthlyTrend($months, $doctorsInPeriod),
                'centers' => $this->monthlyTrend($months, $centersInPeriod),
            ],
            'topCenters' => Payment::query()
                ->succeeded()
                ->selectRaw('clinic_id, sum(amount) as total_revenue, count(*) as payment_count')
                ->groupBy('clinic_id')
                ->with('clinic:id,name')
                ->orderByDesc('total_revenue')
                ->take(5)
                ->get(),
            'topDoctors' => Appointment::query()
                ->visibleToCareTeam()
                ->selectRaw('doctor_id, count(*) as appointment_count')
                ->groupBy('doctor_id')
                ->with('doctor:id,name,specialization')
                ->orderByDesc('appointment_count')
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * @param  Collection<int, Carbon>  $months
     * @param  Collection<int, object>  $records
     * @return array<int, array{label: string, value: int}>
     */
    private function monthlyTrend(Collection $months, Collection $records): array
    {
        $grouped = $records->groupBy(fn (object $record): string => $record->created_at->format('Y-m'));

        return $months->map(fn ($month): array => [
            'label' => $month->format('M'),
            'value' => $grouped->get($month->format('Y-m'), collect())->count(),
        ])->all();
    }
}
