@extends('layouts.medical-center')

@php
    $title = 'Payments';
    $subtitle = 'Clinic revenue and internal doctor payout records';
@endphp

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <x-dashboard.stat-card label="Revenue this month" :value="'LKR '.number_format((float) $revenueThisMonth, 2)" chip="accent" />
        <x-dashboard.stat-card label="Clinic fees collected" :value="'LKR '.number_format((float) $clinicFeesCollected, 2)" chip="emerald" />
        <x-dashboard.stat-card label="Pending doctor payouts" :value="'LKR '.number_format((float) $pendingDoctorPayouts, 2)" chip="amber" />
    </div>

    <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-[11px] leading-relaxed text-blue-900">
        <strong>Internal ledger:</strong> Marking a payout as paid updates PsyCare records only. It does not initiate a Stripe or bank transfer.
    </div>

    <div class="mt-5">
        <x-dashboard.panel title="Doctor payouts" subtitle="Group all currently unpaid appointment earnings by doctor">
            @if ($unpaidPayouts->isEmpty())
                <div class="grid min-h-28 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-5 text-center">
                    <p class="text-[12px] font-medium text-ink">No pending doctor payouts.</p>
                </div>
            @else
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($unpaidPayouts as $row)
                        <article class="rounded-2xl border border-border bg-white p-4">
                            <p class="text-[12px] font-semibold text-ink">Dr. {{ $row->doctor->name }}</p>
                            <p class="mt-1 text-[10px] text-ink-soft">{{ $row->payment_count }} {{ Str::plural('payment', $row->payment_count) }}</p>
                            <p class="mt-3 font-display text-[18px] font-medium text-ink">LKR {{ number_format((float) $row->pending_amount, 2) }}</p>
                            <form method="POST" action="{{ route('medical-center.payments.doctors.mark-paid', $row->doctor) }}" class="mt-4" onsubmit="return confirm('Mark LKR {{ number_format((float) $row->pending_amount, 2) }} as paid to Dr. {{ addslashes($row->doctor->name) }}? This will not process a real bank transfer — please complete the actual payment through your normal payout method and use this only to update your internal records.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full rounded-xl bg-blue-800 px-4 py-2.5 text-[10px] font-semibold tracking-[0.08em] text-white uppercase transition-colors hover:bg-blue-900">Mark as paid</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-dashboard.panel>
    </div>

    <div class="mt-5">
        <x-dashboard.panel title="Payment ledger" subtitle="Succeeded Stripe Checkout payments, newest first">
            <form method="GET" action="{{ route('medical-center.payments.index') }}" class="mb-5 grid gap-3 rounded-2xl bg-secondary/50 p-4 sm:grid-cols-2 xl:grid-cols-5">
                <label class="text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">From<input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3 py-2.5 text-[11px] text-ink"></label>
                <label class="text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">To<input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3 py-2.5 text-[11px] text-ink"></label>
                <label class="text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">Doctor<select name="doctor_id" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3 py-2.5 text-[11px] text-ink"><option value="">All doctors</option>@foreach ($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(($filters['doctor_id'] ?? null) == $doctor->id)>Dr. {{ $doctor->name }}</option>@endforeach</select></label>
                <label class="text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">Payout<select name="payout_status" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3 py-2.5 text-[11px] text-ink"><option value="">Paid & unpaid</option><option value="unpaid" @selected(($filters['payout_status'] ?? null) === 'unpaid')>Unpaid</option><option value="paid" @selected(($filters['payout_status'] ?? null) === 'paid')>Paid</option></select></label>
                <div class="flex items-end gap-2"><label class="min-w-0 flex-1 text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">Patient<input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3 py-2.5 text-[11px] text-ink"></label><button class="rounded-xl bg-ink px-4 py-2.5 text-[10px] font-semibold text-white uppercase">Filter</button></div>
            </form>

            @if ($payments->isEmpty())
                <div class="grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center"><div><p class="text-[12px] font-medium text-ink">No succeeded payments found.</p><p class="mt-1 text-[11px] text-ink-soft">New verified Checkout payments will appear here.</p></div></div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-border">
                    <table class="w-full min-w-[980px] text-left">
                        <thead class="bg-secondary/70 text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Patient</th><th class="px-4 py-3">Doctor</th><th class="px-4 py-3 text-right">Doctor</th><th class="px-4 py-3 text-right">Clinic</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3">Payout</th></tr></thead>
                        <tbody class="divide-y divide-border bg-white">
                            @foreach ($payments as $payment)
                                <tr><td class="whitespace-nowrap px-4 py-3 text-[11px] text-ink-soft">{{ $payment->processed_at->format('j M Y') }}</td><td class="px-4 py-3 text-[12px] font-medium text-ink">{{ $payment->appointment->patient_name }}</td><td class="px-4 py-3 text-[11px] text-ink">Dr. {{ $payment->doctor->name }}</td><td class="px-4 py-3 text-right text-[11px] text-ink">LKR {{ number_format((float) $payment->doctor_amount, 2) }}</td><td class="px-4 py-3 text-right text-[11px] text-ink">LKR {{ number_format((float) $payment->clinic_amount, 2) }}</td><td class="px-4 py-3 text-right text-[11px] font-semibold text-ink">LKR {{ number_format((float) $payment->amount, 2) }}</td><td class="px-4 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-semibold text-emerald-700 uppercase">Succeeded</span></td><td class="px-4 py-3"><span class="rounded-full {{ $payment->doctor_payout_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }} px-2.5 py-1 text-[9px] font-semibold uppercase">{{ $payment->doctor_payout_status }}</span></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($payments->hasPages())<div class="mt-5">{{ $payments->links() }}</div>@endif
            @endif
        </x-dashboard.panel>
    </div>
@endsection
