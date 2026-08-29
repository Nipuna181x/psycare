@extends('layouts.medical-center')

@php
    $title = 'Analytics';
    $subtitle = 'Performance across your clinic, this month';
@endphp

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-dashboard.stat-card
            label="Appointments this month"
            :value="$thisMonth"
            chip="accent"
            accent="clinic"
            :delta="$trendPct !== null ? ($trendPct >= 0 ? '+' : '').$trendPct.'% vs last month' : null"
            :delta-tone="$trendPct !== null && $trendPct < 0 ? 'negative' : 'positive'"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card
            label="Revenue this month"
            :value="'LKR '.number_format((float) $revenueThisMonth, 2)"
            chip="emerald"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card
            label="Cancellation rate"
            :value="$cancellationRate !== null ? $cancellationRate.'%' : '—'"
            chip="rose"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
        </x-dashboard.stat-card>
    </div>
    <p class="mt-2 text-[11px] text-ink-soft">No-show tracking is not currently captured — this is the cancellation rate (cancelled ÷ completed + cancelled).</p>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <x-dashboard.panel title="Busiest doctors" subtitle="Ranked by appointment count">
            <x-dashboard.bar-list
                accent="clinic"
                empty-label="No appointments yet."
                :items="$busiestDoctors->map(fn ($row) => ['label' => $row->doctor?->name ?? 'Unknown', 'value' => $row->total])->all()"
            />
        </x-dashboard.panel>

        <x-dashboard.panel title="Patient volume trend" subtitle="Appointments over the last 6 months">
            <x-dashboard.column-chart accent="clinic" :items="$volumeTrend" />
        </x-dashboard.panel>
    </div>
@endsection
