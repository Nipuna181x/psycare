<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\PaymentIndexRequest;
use App\Models\Doctor;
use App\Models\DoctorPayout;
use App\Models\Payment;
use App\Services\CurrentClinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Financial ownership and doctor payouts are an internal bookkeeping ledger.
 * PsyCare uses one Stripe account, no Stripe Connect, and this controller never
 * initiates a bank transfer. Clinics must pay doctors through their normal method.
 */
class PaymentController extends Controller
{
    public function index(PaymentIndexRequest $request, CurrentClinic $currentClinic): View
    {
        $clinic = $currentClinic->model();
        abort_unless($clinic, 403);
        $filters = $request->validated();

        $clinicPayments = Payment::query()->succeeded()->whereBelongsTo($clinic, 'clinic');

        $payments = (clone $clinicPayments)
            ->with(['appointment:id,patient_name', 'doctor:id,name', 'patient:id,name', 'doctorPayout:id,status,received_at'])
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('processed_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('processed_at', '<=', $to))
            ->when($filters['doctor_id'] ?? null, fn ($query, $doctorId) => $query->where('doctor_id', $doctorId))
            ->when($filters['payout_status'] ?? null, fn ($query, $status) => $query->where('doctor_payout_status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->whereHas('appointment', fn ($appointmentQuery) => $appointmentQuery->where('patient_name', 'like', '%'.$search.'%')))
            ->latest('processed_at')
            ->paginate(20)
            ->withQueryString();

        $doctors = Doctor::query()
            ->whereHas('payments', fn ($query) => $query->succeeded()->whereBelongsTo($clinic, 'clinic'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $unpaidPayouts = (clone $clinicPayments)
            ->unpaidToDoctor()
            ->selectRaw('doctor_id, SUM(doctor_amount) AS pending_amount, COUNT(*) AS payment_count')
            ->groupBy('doctor_id')
            ->with('doctor:id,name')
            ->orderByDesc('pending_amount')
            ->get();

        return view('medical-center.payments.index', [
            'payments' => $payments,
            'doctors' => $doctors,
            'filters' => $filters,
            'unpaidPayouts' => $unpaidPayouts,
            'revenueThisMonth' => (clone $clinicPayments)->whereBetween('processed_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'clinicFeesCollected' => (clone $clinicPayments)->sum('clinic_amount'),
            'pendingDoctorPayouts' => (clone $clinicPayments)->unpaidToDoctor()->sum('doctor_amount'),
        ]);
    }

    public function markDoctorPaid(Doctor $doctor, CurrentClinic $currentClinic): RedirectResponse
    {
        $clinic = $currentClinic->model();
        abort_unless($clinic, 403);

        $payout = DB::transaction(function () use ($clinic, $doctor): ?DoctorPayout {
            $payments = Payment::query()
                ->succeeded()
                ->unpaidToDoctor()
                ->whereBelongsTo($clinic, 'clinic')
                ->whereBelongsTo($doctor)
                ->lockForUpdate()
                ->get();

            if ($payments->isEmpty()) {
                return null;
            }

            $staff = Auth::guard('clinic_staff')->user();
            $actor = $staff ?? $clinic;
            $payout = DoctorPayout::query()->create([
                'clinic_id' => $clinic->id,
                'doctor_id' => $doctor->id,
                'marked_by_type' => $staff ? 'clinic_staff' : 'medical_center',
                'marked_by_id' => $actor->id,
                'marked_by_name' => $actor->name,
                'amount' => $payments->sum('doctor_amount'),
                'payment_count' => $payments->count(),
                'paid_at' => now(),
            ]);

            Payment::query()->whereKey($payments->modelKeys())->update([
                'doctor_payout_status' => 'paid',
                'doctor_paid_at' => $payout->paid_at,
                'doctor_payout_id' => $payout->id,
            ]);

            return $payout;
        }, attempts: 3);

        if (! $payout) {
            return back()->with('status', 'There are no unpaid succeeded payments for this doctor.');
        }

        return back()->with('status', 'Recorded LKR '.number_format((float) $payout->amount, 2).' as paid to Dr. '.$doctor->name.'.');
    }
}
