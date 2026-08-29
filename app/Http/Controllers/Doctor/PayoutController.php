<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPayout;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Read-only internal payout ledger. PsyCare uses one Stripe account and no
 * Stripe Connect; clinics mark externally completed doctor payments as paid.
 * This controller never calls a transfer or payout API.
 */
class PayoutController extends Controller
{
    public function index(): View
    {
        $doctor = Auth::guard('doctor')->user();
        $payments = Payment::query()->succeeded()->whereBelongsTo($doctor);

        $pendingByClinic = (clone $payments)
            ->unpaidToDoctor()
            ->selectRaw('clinic_id, SUM(doctor_amount) AS pending_amount, COUNT(*) AS payment_count')
            ->groupBy('clinic_id')
            ->with('clinic:id,name')
            ->orderByDesc('pending_amount')
            ->get();

        $history = DoctorPayout::query()
            ->whereBelongsTo($doctor)
            ->with('clinic:id,name')
            ->latest('paid_at')
            ->paginate(20);

        return view('doctor.payouts.index', [
            'pendingByClinic' => $pendingByClinic,
            'history' => $history,
            'totalPending' => (clone $payments)->unpaidToDoctor()->sum('doctor_amount'),
            'totalPaid' => (clone $payments)->where('doctor_payout_status', 'paid')->sum('doctor_amount'),
            'paidThisMonth' => (clone $payments)
                ->where('doctor_payout_status', 'paid')
                ->whereBetween('doctor_paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('doctor_amount'),
        ]);
    }
}
