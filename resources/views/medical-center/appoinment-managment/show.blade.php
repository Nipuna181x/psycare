@extends('layouts.medical-center')

@php
    $title = 'Appointment #'.str_pad($appointment->id, 6, '0', STR_PAD_LEFT);
    $subtitle = $appointment->patient_name.' with '.$appointment->doctor->name;
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Visit details" class="lg:col-span-2">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Doctor</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->doctor->name }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Patient</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_name }}@if ($appointment->patient_age) &middot; {{ $appointment->patient_age }} yrs @endif</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Contact</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_phone }}</dd>
                    <dd class="text-[12px] text-ink-soft">{{ $appointment->patient_email ?? 'No email provided' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Date & time</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}, {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
                </div>
            </dl>
            @if ($appointment->reason)
                <div class="mt-4 rounded-2xl bg-secondary p-4">
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Reason for visit</dt>
                    <dd class="mt-1 text-[13px] text-ink">{{ $appointment->reason }}</dd>
                </div>
            @endif
            <div class="mt-4 flex items-center gap-2">
                <x-dashboard.badge :status="$appointment->status" />
                @if ($appointment->pre_assessment_risk_level)
                    <x-dashboard.badge :status="$appointment->pre_assessment_risk_level" />
                @endif
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="AI pre-assessment">
            @if ($appointment->pre_assessment_summary)
                <p class="text-[12px] leading-relaxed text-ink">{{ $appointment->pre_assessment_summary }}</p>
            @else
                <p class="text-[12px] text-ink-soft">Not assessed.</p>
            @endif
        </x-dashboard.panel>
    </div>
@endsection
