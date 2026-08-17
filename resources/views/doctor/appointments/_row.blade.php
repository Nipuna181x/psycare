<li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="flex min-w-0 items-center gap-3">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-sky-100 text-[12px] font-semibold text-sky-700">{{ mb_strtoupper(mb_substr($appointment->patient_name, 0, 1)) }}</span>
        <div class="min-w-0">
            <p class="truncate text-[13px] font-medium text-ink">{{ $appointment->patient_name }}</p>
            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }} · {{ $appointment->mode === 'online' ? 'Online' : 'In person' }}</p>
        </div>
    </a>
    <div class="flex items-center gap-2">
        @if ($appointment->pre_assessment_risk_level)
            <x-dashboard.badge :status="$appointment->pre_assessment_risk_level" />
        @endif
        <x-dashboard.badge :status="$appointment->status" />
    </div>
</li>
