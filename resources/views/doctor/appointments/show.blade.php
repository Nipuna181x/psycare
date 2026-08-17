@extends('layouts.doctor')

@php
    $title = 'Appointment #'.str_pad($appointment->id, 6, '0', STR_PAD_LEFT);
    $subtitle = $appointment->patient_name;
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Visit details" class="lg:col-span-2">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Patient</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_name }}@if ($appointment->patient_age) &middot; {{ $appointment->patient_age }} yrs @endif @if ($appointment->patient_gender) &middot; {{ ucfirst($appointment->patient_gender) }} @endif</dd>
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
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Mode</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->mode === 'online' ? 'Online' : 'In person' }}</dd>
                </div>
            </dl>
            @if ($appointment->reason)
                <div class="mt-4 rounded-2xl bg-secondary p-4">
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Reason for visit</dt>
                    <dd class="mt-1 text-[13px] text-ink">{{ $appointment->reason }}</dd>
                </div>
            @endif

            @if ($appointment->status === 'confirmed')
                <div class="mt-6 flex items-center gap-3 border-t border-border pt-5">
                    <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Mark completed</button>
                    </form>
                    <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Cancel appointment</button>
                    </form>
                </div>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel title="AI pre-assessment">
            <div class="flex items-center justify-between gap-3">
                <p class="text-[12px] text-ink-soft">Mood rating</p>
                <p class="font-display text-[16px] font-medium text-ink">{{ $appointment->pre_assessment_mood_rating ?? '—' }}/10</p>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
                <p class="text-[12px] text-ink-soft">Risk level</p>
                @if ($appointment->pre_assessment_risk_level)
                    <x-dashboard.badge :status="$appointment->pre_assessment_risk_level" />
                @else
                    <span class="text-[12px] text-ink-soft">Not assessed</span>
                @endif
            </div>

            @if ($appointment->pre_assessment_summary)
                <p class="mt-4 rounded-2xl bg-secondary p-4 text-[12px] leading-relaxed text-ink">{{ $appointment->pre_assessment_summary }}</p>
            @endif

            @if ($appointment->pre_assessment)
                <ul class="mt-4 space-y-3">
                    @foreach ($appointment->pre_assessment as $answer)
                        <li>
                            <p class="text-[12px] font-medium text-ink">{{ $answer['question'] }}</p>
                            <p class="mt-0.5 text-[12px] text-ink-soft">{{ $answer['answer'] !== '' ? $answer['answer'] : 'Skipped' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>
@endsection
