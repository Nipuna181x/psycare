@extends('layouts.medical-center')

@php
    $title = $patient->name;
    $subtitle = 'Appointment history at your clinic';
    $latest = $appointments->first();
@endphp

@section('content')
    <div class="grid gap-5">
        <nav aria-label="Breadcrumb">
            <a href="{{ route('medical-center.patients.index') }}" class="inline-flex items-center gap-2 text-[12px] font-medium text-blue-800 transition-colors hover:text-blue-900">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to patients
            </a>
        </nav>

        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <div class="flex items-center gap-3.5">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-blue-100 text-[15px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($patient->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <h2 class="truncate font-display text-[17px] font-medium text-ink">{{ $patient->name }}</h2>
                    <p class="mt-0.5 text-[12px] text-ink-soft">
                        @if ($latest?->patient_age) {{ $latest->patient_age }} yrs @endif
                        @if ($latest?->patient_gender) · {{ ucfirst($latest->patient_gender) }} @endif
                    </p>
                </div>
            </div>
            <dl class="mt-5 grid grid-cols-2 gap-4 text-[12px] sm:grid-cols-3">
                <div>
                    <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Appointments here</dt>
                    <dd class="mt-1 font-medium text-ink">{{ $appointments->count() }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Most recent visit</dt>
                    <dd class="mt-1 font-medium text-ink">{{ $latest?->appointment_date->format('D, j M Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Treating doctor</dt>
                    <dd class="mt-1 font-medium text-ink">{{ $latest?->doctor->name ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <div class="grid gap-5">
            @forelse ($appointments as $appointment)
                <details class="group rounded-3xl bg-card shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)]" @if ($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5 marker:content-none md:p-6">
                        <div>
                            <p class="text-[13px] font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }} with {{ $appointment->doctor->name }}</p>
                            <p class="mt-0.5 text-[11px] text-ink-soft">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-dashboard.badge :status="$appointment->status" />
                            <svg class="h-4 w-4 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </summary>
                    <div class="grid gap-5 border-t border-border p-5 md:p-6">
                        <x-appointment.crisis-banner :appointment="$appointment" />
                        <x-appointment.visit-details-card :appointment="$appointment" />
                        <x-appointment.prescription-card :appointment="$appointment" :editable="false" />
                        <x-appointment.screener-panel :appointment="$appointment" />
                    </div>
                </details>
            @empty
                <p class="rounded-3xl bg-card p-8 text-center text-[13px] text-ink-soft">No appointments at your clinic yet.</p>
            @endforelse
        </div>
    </div>
@endsection
