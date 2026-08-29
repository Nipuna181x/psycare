@props(['appointment'])

<section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="visit-details-heading">
    <h2 id="visit-details-heading" class="font-display text-[15px] font-medium text-ink">Visit details</h2>
    <dl class="mt-5 divide-y divide-border">
        <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
            <dt class="text-[11px] text-ink-soft">Date</dt>
            <dd class="text-right text-[12px] font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}</dd>
        </div>
        <div class="flex items-center justify-between gap-4 py-3">
            <dt class="text-[11px] text-ink-soft">Time</dt>
            <dd class="text-right text-[12px] font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
        </div>
        <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
            <dt class="text-[11px] text-ink-soft">Status</dt>
            <dd><x-dashboard.badge :status="$appointment->status" /></dd>
        </div>
    </dl>

    @if ($appointment->reason)
        <div class="mt-5 border-t border-border pt-5">
            <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Reason for visit</p>
            <p class="mt-2 text-[12px] leading-relaxed text-ink">{{ $appointment->reason }}</p>
        </div>
    @endif
</section>
