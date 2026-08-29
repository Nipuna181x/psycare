@props(['doctor', 'context' => 'search', 'existingAffiliation' => null, 'clinicAppointmentCount' => null, 'sendRequestRoute' => null])

<dialog id="doctor-{{ $doctor->id }}" class="fixed inset-0 m-auto w-[min(32rem,calc(100vw-2rem))] rounded-3xl bg-card p-0 shadow-2xl backdrop:bg-ink/40">
    <div class="max-h-[85vh] overflow-y-auto p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3.5">
                @if ($doctor->avatarUrl())
                    <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" class="h-14 w-14 rounded-2xl object-cover">
                @else
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-100 text-[15px] font-semibold text-blue-800">{{ $doctor->initials() }}</span>
                @endif
                <div class="min-w-0">
                    <h2 class="truncate font-display text-[17px] font-medium text-ink">{{ $doctor->name }}</h2>
                    <p class="mt-0.5 truncate text-[12px] text-ink-soft">{{ $doctor->specialization ?? 'General practice' }}</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('doctor-{{ $doctor->id }}').close()" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-secondary text-ink-soft hover:bg-secondary/80" aria-label="Close">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        @if ($doctor->bio)
            <p class="mt-4 text-[12px] leading-relaxed text-ink-soft">{{ $doctor->bio }}</p>
        @endif

        <dl class="mt-5 grid grid-cols-2 gap-4 text-[12px]">
            <div>
                <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Licence no.</dt>
                <dd class="mt-1 font-medium text-ink">{{ $doctor->license_number }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Experience</dt>
                <dd class="mt-1 font-medium text-ink">{{ $doctor->years_of_experience ? $doctor->years_of_experience.'+ yrs' : '—' }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Session fee</dt>
                <dd class="mt-1 font-medium text-ink">{{ $doctor->isPriced() ? 'LKR '.number_format((float) $doctor->consultation_fee, 2) : 'Not set' }}</dd>
            </div>
            <div>
                <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Consultation mode</dt>
                <dd class="mt-1 font-medium text-ink">{{ $doctor->consultationModeLabel() }}</dd>
            </div>
            @if ($doctor->rating)
                <div>
                    <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Rating</dt>
                    <dd class="mt-1 font-medium text-ink">{{ number_format((float) $doctor->rating, 1) }} / 5</dd>
                </div>
            @endif
            @if ($context === 'my-doctors')
                <div>
                    <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">At your clinic</dt>
                    <dd class="mt-1 font-medium text-ink">{{ $clinicAppointmentCount ?? 0 }} appointment(s)</dd>
                </div>
            @else
                <div>
                    <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Active at</dt>
                    <dd class="mt-1 font-medium text-ink">{{ $doctor->active_affiliations_count ?? 0 }} clinic(s)</dd>
                </div>
            @endif
        </dl>

        <div class="mt-6">
            @if ($context === 'my-doctors')
                <span class="block rounded-xl bg-emerald-100 px-4 py-2.5 text-center text-[11px] font-semibold tracking-[0.08em] text-emerald-700 uppercase">Active at your clinic</span>
            @elseif ($existingAffiliation)
                <span class="block rounded-xl bg-secondary px-4 py-2.5 text-center text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">
                    {{ match ($existingAffiliation->status) {
                        'requested' => 'Request pending',
                        'active' => 'Already affiliated',
                        'declined' => 'Request declined',
                        default => ucfirst($existingAffiliation->status),
                    } }}
                </span>
            @elseif ($sendRequestRoute)
                <form method="POST" action="{{ $sendRequestRoute }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-blue-800 px-4 py-3 text-[11px] font-semibold tracking-[0.08em] text-white uppercase hover:bg-blue-900">Send Work Request</button>
                </form>
            @endif
        </div>
    </div>
</dialog>
