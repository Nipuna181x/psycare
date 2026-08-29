@props(['appointment'])

@if ($appointment->requiresCrisisEscalation())
    <section role="alert" class="rounded-3xl border border-red-200 bg-red-50 p-5 shadow-[0_14px_35px_-28px_rgba(185,28,28,0.5)] md:p-6">
        <div class="flex items-start gap-4">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-red-100 text-red-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.3 2.86 1.82 17a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 2.86a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-display text-[16px] font-semibold text-red-900">Immediate clinical review required</h2>
                    <span class="rounded-full bg-red-200 px-2.5 py-1 text-[9px] font-bold tracking-[0.08em] text-red-800 uppercase">Elevated risk</span>
                </div>
                <p class="mt-2 max-w-[90ch] text-[13px] leading-relaxed text-red-800">The pre-assessment indicates elevated risk, including a positive response relating to death or self-harm. Review the full responses and follow the crisis escalation workflow before continuing with routine visit preparation.</p>
            </div>
        </div>
    </section>
@endif
