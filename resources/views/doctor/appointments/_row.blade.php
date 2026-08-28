@php
    $riskLevel = $appointment->pre_assessment_risk_level ?? 'unrated';
    $riskTone = match ($riskLevel) {
        'low' => 'bg-emerald-500',
        'moderate' => 'bg-amber-500',
        'elevated' => 'bg-red-500',
        default => 'bg-slate-300',
    };
@endphp

<li
    data-appointment-row
    data-status="{{ $appointment->status }}"
    data-risk="{{ $riskLevel }}"
    data-date="{{ $appointment->appointment_date->format('Y-m-d') }}"
    class="group relative rounded-2xl border border-border bg-white transition-[border-color,box-shadow,translate] duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-[0_14px_30px_-24px_rgba(14,116,144,0.45)] focus-within:border-sky-300 focus-within:ring-2 focus-within:ring-sky-500/20"
>
    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="flex flex-col gap-4 rounded-2xl p-4 outline-none sm:flex-row sm:items-center sm:justify-between" aria-label="View appointment with {{ $appointment->patient_name }}">
        <div class="flex min-w-0 items-start gap-3.5 sm:items-center">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-[13px] font-semibold text-sky-700 ring-1 ring-sky-100">{{ mb_strtoupper(mb_substr($appointment->patient_name, 0, 1)) }}</span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate text-[13px] font-semibold text-ink">{{ $appointment->patient_name }}</p>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold tracking-[0.06em] uppercase @if ($riskLevel === 'elevated') text-red-700 @elseif ($riskLevel === 'moderate') text-amber-700 @elseif ($riskLevel === 'low') text-emerald-700 @else text-ink-soft @endif">
                        <span class="h-2 w-2 rounded-full {{ $riskTone }}" aria-hidden="true"></span>
                        {{ $riskLevel === 'unrated' ? 'Not assessed' : ucfirst($riskLevel).' risk' }}
                    </span>
                </div>
                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-ink-soft">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
                        {{ $appointment->appointment_date->format('D, j M') }} at {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        @if ($appointment->mode === 'online')
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 13 5 3V8l-5 3"/><rect width="13" height="12" x="3" y="6" rx="2"/></svg>
                            Video consultation
                        @else
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s6-4.35 6-11a6 6 0 1 0-12 0c0 6.65 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/></svg>
                            In person
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2 pl-14 sm:justify-end sm:pl-0">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-secondary px-2.5 py-1 text-[10px] font-medium text-ink-soft">
                <span class="h-1.5 w-1.5 rounded-full {{ $appointment->screener_completed_at ? 'bg-emerald-500' : 'bg-amber-500' }}" aria-hidden="true"></span>
                Pre-assessment: {{ $appointment->screener_completed_at ? 'Ready' : 'Pending' }}
            </span>
            <x-dashboard.badge :status="$appointment->status" />
            <svg class="ml-1 h-4 w-4 text-ink-soft transition-[translate,color] group-hover:translate-x-0.5 group-hover:text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </div>
    </a>
</li>
