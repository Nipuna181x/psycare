@extends('layouts.doctor')

@php
    $title = 'Crisis Queue';
    $subtitle = 'Urgent pre-assessments awaiting clinical review';
@endphp

@section('content')
    @if (session('status'))
        <div class="mb-5 rounded-2xl bg-emerald-50 px-4 py-3 text-[13px] text-emerald-700">{{ session('status') }}</div>
    @endif

    <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-[10px] font-semibold tracking-[0.12em] text-red-700 uppercase">Clinical priority</p><h2 class="mt-1 font-display text-[17px] font-medium text-ink">Awaiting review</h2><p class="mt-1 text-[12px] text-ink-soft">Latest flagged assessment per patient, using the appointment escalation criteria.</p></div>
            <div class="inline-flex self-start rounded-xl bg-secondary p-1 text-[10px] font-semibold uppercase tracking-[0.06em]">
                <a href="{{ route('doctor.crisis-queue.index', ['sort' => 'recent']) }}" class="rounded-lg px-3 py-2 {{ $sort === 'recent' ? 'bg-white text-sky-700 shadow-sm' : 'text-ink-soft' }}">Most recent</a>
                <a href="{{ route('doctor.crisis-queue.index', ['sort' => 'overdue']) }}" class="rounded-lg px-3 py-2 {{ $sort === 'overdue' ? 'bg-white text-sky-700 shadow-sm' : 'text-ink-soft' }}">Most overdue</a>
            </div>
        </div>

        @if ($unreviewed->isEmpty())
            <div class="mt-6 grid min-h-56 place-items-center rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/50 p-8 text-center">
                <div><span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-emerald-700"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span><h3 class="mt-4 font-display text-[16px] font-medium text-ink">No urgent reviews right now.</h3><p class="mt-1 text-[12px] text-ink-soft">New elevated-risk assessments will appear here immediately.</p></div>
            </div>
        @else
            <div class="mt-6 grid gap-3">
                @foreach ($unreviewed as $appointment)
                    <article class="rounded-2xl border border-red-100 bg-white p-4 shadow-[0_8px_24px_-22px_rgba(127,29,29,0.4)]">
                        <div class="grid gap-4 lg:grid-cols-[1.3fr_repeat(3,minmax(100px,0.6fr))_auto] lg:items-center">
                            <div><div class="flex flex-wrap items-center gap-2"><h3 class="text-[13px] font-semibold text-ink">{{ $appointment->patient_name }}</h3><x-dashboard.badge :status="$appointment->pre_assessment_risk_level ?? 'elevated'" /></div><p class="mt-1 text-[11px] text-red-700">Flagged {{ $appointment->screener_completed_at?->diffForHumans() ?? 'recently' }}</p></div>
                            <div><p class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">PHQ-9</p><p class="mt-1 text-[13px] font-semibold text-ink">{{ $appointment->phq9_total ?? '—' }}<span class="text-[10px] font-normal text-ink-soft"> / 27</span></p></div>
                            <div><p class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">GAD-7</p><p class="mt-1 text-[13px] font-semibold text-ink">{{ $appointment->gad7_total ?? '—' }}<span class="text-[10px] font-normal text-ink-soft"> / 21</span></p></div>
                            <div><p class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Appointment</p><p class="mt-1 text-[11px] font-medium text-ink">{{ $appointment->appointment_date->format('j M Y') }}</p></div>
                            <div class="flex flex-wrap gap-2 lg:justify-end"><a href="{{ route('doctor.appointments.show', $appointment) }}" class="rounded-xl bg-sky-700 px-4 py-2.5 text-[10px] font-semibold tracking-[0.08em] text-white uppercase hover:bg-sky-800">Review</a><form method="POST" action="{{ route('doctor.crisis-queue.acknowledge', $appointment) }}">@csrf @method('PATCH')<button class="rounded-xl border border-border bg-white px-4 py-2.5 text-[10px] font-semibold tracking-[0.08em] text-ink uppercase hover:bg-secondary">Acknowledge</button></form></div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if ($reviewed->isNotEmpty())
        <details class="mt-5 rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <summary class="flex cursor-pointer list-none items-center justify-between"><div><h2 class="font-display text-[16px] font-medium text-ink">Reviewed</h2><p class="mt-1 text-[11px] text-ink-soft">Retained for the clinical audit trail.</p></div><span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft">{{ $reviewed->count() }}</span></summary>
            <div class="mt-4 divide-y divide-border border-t border-border">
                @foreach ($reviewed as $appointment)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3"><div><p class="text-[12px] font-medium text-ink">{{ $appointment->patient_name }}</p><p class="mt-1 text-[10px] text-ink-soft">Acknowledged {{ $appointment->escalation_reviewed_at?->diffForHumans() }}</p></div><a href="{{ route('doctor.appointments.show', $appointment) }}" class="text-[10px] font-semibold tracking-[0.08em] text-sky-700 uppercase">Open record</a></div>
                @endforeach
            </div>
        </details>
    @endif
@endsection
