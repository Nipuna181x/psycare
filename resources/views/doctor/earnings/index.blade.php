@extends('layouts.doctor')

@php
    $title = 'Earnings';
    $subtitle = 'Your combined earnings across every clinic';
@endphp

@section('content')
    <div class="grid gap-5 sm:grid-cols-2">
        <x-dashboard.stat-card label="Total earned" :value="'LKR '.number_format($totalEarned)" chip="emerald">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </x-dashboard.stat-card>
        <x-dashboard.stat-card label="This month" :value="'LKR '.number_format($thisMonthEarned)" chip="accent" accent="doctor">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
        </x-dashboard.stat-card>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Earnings over the last 6 months" class="lg:col-span-2">
            <x-dashboard.column-chart
                accent="doctor"
                :items="collect($monthlyChart)->map(fn ($month) => ['label' => $month['label'], 'value' => (int) $month['value']])->all()"
            />
        </x-dashboard.panel>

        <x-dashboard.panel title="Per-clinic summary" subtitle="Total earned at each clinic">
            @if ($perClinic->isEmpty())
                <p class="text-[13px] text-ink-soft">No clinic activity yet.</p>
            @else
                <ul class="divide-y divide-border">
                    @foreach ($perClinic as $row)
                        <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <p class="truncate text-[12px] font-medium text-ink">{{ $row['clinic']->name ?? 'Clinic' }}</p>
                            <p class="shrink-0 text-[12px] font-medium text-ink">LKR {{ number_format($row['total']) }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>

    <div class="mt-5">
        <x-dashboard.panel title="Appointment breakdown" subtitle="Every appointment contributing to your earnings">
            @if ($breakdown->isEmpty())
                <div class="grid min-h-32 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center">
                    <div>
                        <svg class="mx-auto h-6 w-6 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <p class="mt-2 text-[12px] font-medium text-ink">No earnings yet</p>
                        <p class="mt-1 text-[11px] text-ink-soft">Completed appointments will appear here.</p>
                    </div>
                </div>
            @else
                <ul class="divide-y divide-border">
                    @foreach ($breakdown as $appointment)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $appointment->patient_name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ $appointment->medicalCenter->name ?? 'Clinic' }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <p class="text-[13px] font-medium text-ink">LKR {{ number_format($appointment->doctor_fee_charged) }}</p>
                                <x-dashboard.badge :status="$appointment->status" />
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>
@endsection
