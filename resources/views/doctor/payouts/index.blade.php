@extends('layouts.doctor')

@php
    $title = 'Payouts';
    $subtitle = 'What clinics have paid versus what remains pending';
@endphp

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <x-dashboard.stat-card label="Total pending" :value="'LKR '.number_format((float) $totalPending, 2)" chip="amber" />
        <x-dashboard.stat-card label="Paid all-time" :value="'LKR '.number_format((float) $totalPaid, 2)" chip="emerald" />
        <x-dashboard.stat-card label="Paid this month" :value="'LKR '.number_format((float) $paidThisMonth, 2)" chip="accent" accent="doctor" />
    </div>

    <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-[11px] leading-relaxed text-sky-900">
        Each clinic records when it sends a payout. Once the money reaches you, use <strong>I've received it</strong> to complete the audit record. This does not move money.
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[0.9fr_1.1fr] xl:items-start">
        <x-dashboard.panel title="Pending" subtitle="Unpaid earnings grouped by clinic">
            @if ($pendingByClinic->isEmpty())
                <div class="grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center"><div><span class="text-xl text-emerald-600">✓</span><p class="mt-2 text-[12px] font-medium text-ink">No pending payouts.</p></div></div>
            @else
                <div class="grid gap-3">
                    @foreach ($pendingByClinic as $row)
                        <article class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-border bg-white p-4">
                            <div><p class="text-[12px] font-semibold text-ink">{{ $row->clinic->name }}</p><p class="mt-1 text-[10px] text-ink-soft">{{ $row->payment_count }} unpaid {{ Str::plural('appointment', $row->payment_count) }}</p></div>
                            <p class="font-display text-[17px] font-medium text-ink">LKR {{ number_format((float) $row->pending_amount, 2) }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel title="History" subtitle="Clinic-recorded payout batches, most recent first">
            @if ($history->isEmpty())
                <div class="grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center"><p class="text-[12px] font-medium text-ink">No paid payouts recorded yet.</p></div>
            @else
                <div class="overflow-hidden rounded-2xl border border-border">
                    <ul class="divide-y divide-border bg-white">
                        @foreach ($history as $payout)
                            <li class="flex flex-wrap items-center justify-between gap-4 px-4 py-4">
                                <div class="min-w-0 flex-1"><p class="text-[12px] font-semibold text-ink">{{ $payout->clinic->name }}</p><p class="mt-0.5 text-[10px] text-ink-soft">Paid {{ $payout->paid_at->format('D, j M Y · g:i A') }} · {{ $payout->payment_count }} {{ Str::plural('payment', $payout->payment_count) }}</p>@if ($payout->received_at)<p class="mt-1 text-[10px] text-emerald-700">Received {{ $payout->received_at->format('D, j M Y · g:i A') }}</p>@endif</div>
                                <div class="flex flex-wrap items-center justify-end gap-2 text-right">
                                    <div><p class="text-[13px] font-semibold text-ink">LKR {{ number_format((float) $payout->amount, 2) }}</p><span class="mt-1 inline-flex rounded-full {{ $payout->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }} px-2 py-0.5 text-[9px] font-semibold uppercase">{{ $payout->status }}</span></div>
                                    @if ($payout->status === 'paid')
                                        <form method="POST" action="{{ route('doctor.payouts.received', $payout) }}" onsubmit="return confirm('Confirm that you received this LKR {{ number_format((float) $payout->amount, 2) }} payout from {{ addslashes($payout->clinic->name) }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-xl bg-blue-800 px-3.5 py-2.5 text-[9px] font-semibold tracking-[0.06em] text-white uppercase transition-colors hover:bg-blue-900">I've received it</button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @if ($history->hasPages())<div class="mt-5">{{ $history->links() }}</div>@endif
            @endif
        </x-dashboard.panel>
    </div>
@endsection
