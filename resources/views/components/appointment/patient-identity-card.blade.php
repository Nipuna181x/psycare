@props(['appointment', 'profileRoute' => null])

<section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="patient-identity-heading">
    <div class="flex items-center gap-3.5 border-b border-border pb-5">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-sky-100 text-[15px] font-semibold text-sky-700">{{ mb_strtoupper(mb_substr($appointment->patient_name, 0, 1)) }}</span>
        <div class="min-w-0">
            <p class="text-[10px] font-semibold tracking-[0.12em] text-ink-soft uppercase">Patient</p>
            <h2 id="patient-identity-heading" class="mt-0.5 truncate font-display text-[17px] font-medium text-ink">{{ $appointment->patient_name }}</h2>
            <p class="mt-0.5 text-[11px] text-ink-soft">
                {{ $appointment->patient_age ? $appointment->patient_age.' years' : 'Age not provided' }}
                @if ($appointment->patient_gender) · {{ ucfirst($appointment->patient_gender) }} @endif
            </p>
        </div>
    </div>

    <dl class="mt-5 grid gap-4">
        <div>
            <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Phone</dt>
            <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_phone }}</dd>
        </div>
        <div>
            <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Email</dt>
            <dd class="mt-1 break-all text-[12px] text-ink">{{ $appointment->patient_email ?? 'No email provided' }}</dd>
        </div>
    </dl>

    @if ($profileRoute)
        <a href="{{ $profileRoute }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-[11px] font-semibold tracking-[0.08em] text-sky-700 uppercase transition-colors hover:border-sky-300 hover:bg-sky-100 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">
            Patient profile
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
        </a>
    @endif
</section>
