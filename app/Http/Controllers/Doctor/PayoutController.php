<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorPayout;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Internal payout ledger. PsyCare uses one Stripe account and no Stripe
 * Connect; clinics record payment and doctors may only acknowledge receipt.
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

    public function received(DoctorPayout $doctorPayout): RedirectResponse
    {
        abort_unless($doctorPayout->doctor_id === Auth::guard('doctor')->id(), 403);

        $completed = DB::transaction(function () use ($doctorPayout): bool {
            $payout = DoctorPayout::query()->lockForUpdate()->findOrFail($doctorPayout->id);

            if ($payout->status === 'completed') {
                return false;
            }

            abort_unless($payout->status === 'paid', 422);
            $payout->update(['status' => 'completed', 'received_at' => now()]);

            return true;
        }, attempts: 3);

        return back()->with('status', $completed
            ? 'Payout marked as received.'
            : 'This payout was already marked as received.');
    }
}
