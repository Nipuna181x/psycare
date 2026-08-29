@extends('layouts.admin')

@php
    $title = 'Reports & Analytics';
    $subtitle = 'Platform-wide growth, care activity and payment performance';
@endphp

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dashboard.stat-card label="Registered patients" :value="$totalPatients" chip="accent" accent="admin" />
        <x-dashboard.stat-card label="Approved doctors" :value="$approvedDoctors" chip="emerald" />
        <x-dashboard.stat-card label="Approved centers" :value="$approvedCenters" chip="amber" />
        <x-dashboard.stat-card label="Processed payments" :value="'LKR '.number_format((float) $succeededRevenue, 0)" chip="rose" :delta="'LKR '.number_format((float) $revenueThisMonth, 0).' this month'" />
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Appointment growth" subtitle="New bookings created over the last six months" class="lg:col-span-2">
            <x-dashboard.column-chart accent="admin" :items="$appointmentTrend" />
        </x-dashboard.panel>
        <x-dashboard.panel title="Appointment status" subtitle="All care-team-visible appointments">
            <x-dashboard.bar-list accent="admin" empty-label="No appointments yet." :items="collect(['confirmed', 'completed', 'cancelled'])->map(fn ($key) => ['label' => ucfirst($key), 'value' => $appointmentStatuses[$key] ?? 0])->all()" />
        </x-dashboard.panel>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Patient registrations" subtitle="Last six months"><x-dashboard.column-chart accent="admin" :items="$registrationTrend['patients']" /></x-dashboard.panel>
        <x-dashboard.panel title="Doctor registrations" subtitle="Last six months"><x-dashboard.column-chart accent="admin" :items="$registrationTrend['doctors']" /></x-dashboard.panel>
        <x-dashboard.panel title="Center registrations" subtitle="Last six months"><x-dashboard.column-chart accent="admin" :items="$registrationTrend['centers']" /></x-dashboard.panel>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Pre-assessment risk" subtitle="Recorded clinical risk levels">
            <div class="space-y-3">
                @foreach (['low', 'moderate', 'elevated'] as $risk)
                    <div class="flex items-center justify-between rounded-2xl bg-secondary px-4 py-3"><x-dashboard.badge :status="$risk" /><span class="text-[13px] font-semibold text-ink">{{ $riskCounts[$risk] ?? 0 }}</span></div>
                @endforeach
            </div>
        </x-dashboard.panel>
        <x-dashboard.panel title="Top medical centers" subtitle="By succeeded payment value">
            <div class="space-y-3">@forelse ($topCenters as $row)<div class="flex items-center justify-between gap-3 border-b border-border pb-3 last:border-0 last:pb-0"><div class="min-w-0"><p class="truncate text-[12px] font-semibold text-ink">{{ $row->clinic?->name ?? 'Unavailable center' }}</p><p class="mt-1 text-[10px] text-ink-soft">{{ $row->payment_count }} payments</p></div><span class="shrink-0 text-[11px] font-semibold text-ink">LKR {{ number_format((float) $row->total_revenue, 0) }}</span></div>@empty<p class="text-[12px] text-ink-soft">No succeeded payments yet.</p>@endforelse</div>
        </x-dashboard.panel>
        <x-dashboard.panel title="Busiest doctors" subtitle="By appointment volume">
            <div class="space-y-3">@forelse ($topDoctors as $row)<div class="flex items-center justify-between gap-3 border-b border-border pb-3 last:border-0 last:pb-0"><div class="min-w-0"><p class="truncate text-[12px] font-semibold text-ink">Dr. {{ $row->doctor?->name ?? 'Unavailable' }}</p><p class="mt-1 text-[10px] text-ink-soft">{{ $row->doctor?->specialization ?? 'Unspecified' }}</p></div><span class="shrink-0 rounded-full bg-ink/10 px-2.5 py-1 text-[10px] font-semibold text-ink">{{ $row->appointment_count }}</span></div>@empty<p class="text-[12px] text-ink-soft">No appointments yet.</p>@endforelse</div>
        </x-dashboard.panel>
    </div>
@endsection
