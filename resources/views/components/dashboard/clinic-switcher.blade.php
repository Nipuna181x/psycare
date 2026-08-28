@props(['clinics', 'activeClinicId'])

@if ($clinics->count() > 1)
    <details class="group relative">
        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full bg-secondary px-3.5 py-2 text-[12px] font-medium text-ink marker:content-none">
            {{ $activeClinicId ? $clinics->firstWhere('clinic_id', $activeClinicId)?->clinic?->name : 'All clinics' }}
            <svg class="h-3.5 w-3.5 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        </summary>
        <div class="absolute left-0 z-30 mt-2 w-56 rounded-2xl border border-border bg-card p-2 shadow-xl">
            <form method="POST" action="{{ route('doctor.clinic-context.update') }}">
                @csrf
                <button type="submit" name="clinic_id" value="" class="block w-full rounded-xl px-3 py-2.5 text-left text-[12px] hover:bg-secondary {{ ! $activeClinicId ? 'font-semibold text-sky-700' : 'text-ink' }}">All clinics</button>
            </form>
            @foreach ($clinics as $affiliation)
                <form method="POST" action="{{ route('doctor.clinic-context.update') }}">
                    @csrf
                    <button type="submit" name="clinic_id" value="{{ $affiliation->clinic_id }}" class="block w-full truncate rounded-xl px-3 py-2.5 text-left text-[12px] hover:bg-secondary {{ $activeClinicId === $affiliation->clinic_id ? 'font-semibold text-sky-700' : 'text-ink' }}">{{ $affiliation->clinic->name }}</button>
                </form>
            @endforeach
        </div>
    </details>
@endif
