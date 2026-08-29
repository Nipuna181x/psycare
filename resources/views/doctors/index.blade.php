<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find & Book a Doctor — PsyCare Sri Lanka</title>
    <meta name="description" content="Search every registered psychiatrist, psychologist and counsellor in Sri Lanka by specialty, then book an appointment in minutes.">
    <meta property="og:title" content="Find & Book a Doctor — PsyCare Sri Lanka">
    <meta property="og:description" content="Filter clinicians by specialty and reserve the next open slot.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink selection:bg-teal/20">
        <x-patient-nav />

        <main class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
            <header class="max-w-[38ch]"><p class="eyebrow">Appointments</p><h1 class="display-head mt-3 text-[clamp(2.1rem,4.6vw,3.6rem)] text-ink">Book any doctor, any clinic, one calm search</h1></header>

            <form id="doctor-filters" class="mt-10 rounded-3xl bg-card p-4 md:p-5">
                <div class="grid gap-3 md:grid-cols-[1.6fr_auto]">
                    <label class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5">
                        <svg class="h-4 w-4 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <span class="sr-only">Doctor, clinic or concern</span>
                        <input id="query-filter" placeholder="Doctor, clinic or concern" class="w-full bg-transparent text-[13px] text-ink placeholder:text-muted-foreground outline-none">
                    </label>
                    <button type="submit" class="rounded-2xl bg-ink px-7 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Search</button>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">
                    @foreach (['All', ...$specializations->all()] as $specialty)
                        <button type="button" data-specialty="{{ $specialty }}" class="rounded-full px-4 py-2 text-[12px]">{{ $specialty }}</button>
                    @endforeach
                </div>
                <div class="mt-4 grid gap-3 border-t border-border pt-4 sm:grid-cols-2">
                    <label class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3">
                        <svg class="h-4 w-4 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <select id="city-filter" class="w-full bg-transparent text-[13px] text-ink outline-none">
                            <option value="All">All locations</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3">
                        <svg class="h-4 w-4 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
                        <input id="date-filter" type="date" min="{{ now()->toDateString() }}" class="w-full bg-transparent text-[13px] text-ink outline-none" placeholder="Any date">
                        <button type="button" id="date-filter-clear" hidden class="shrink-0 text-[11px] text-ink-soft underline-offset-4 hover:underline">Clear</button>
                    </label>
                </div>
            </form>

            <p id="results-count" class="mt-8 text-[13px] text-ink-soft"></p>
            <div id="doctor-grid" class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($doctors as $doctor)
                    @php
                        $clinicNames = $doctor->activeAffiliations->pluck('clinic.name')->implode(', ');
                        $doctorCities = $doctor->activeAffiliations->pluck('clinic.address')->filter()->map(fn ($address) => trim(Str::afterLast($address, ',')))->filter()->unique()->values();
                        $availableDates = $doctor->availabilitySlots->pluck('date')->map(fn ($date) => $date->toDateString())->unique()->values();
                    @endphp
                    <article
                        data-doctor
                        data-specialty="{{ $doctor->specialization ?? '' }}"
                        data-search="{{ Str::lower($doctor->name.' '.$clinicNames.' '.$doctor->specialization) }}"
                        data-cities="{{ $doctorCities->implode('|') }}"
                        data-available-dates="{{ $availableDates->implode('|') }}"
                        class="group overflow-hidden rounded-3xl bg-card"
                    >
                        <div class="relative overflow-hidden bg-secondary">
                            @if ($doctor->avatarUrl())
                                <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" width="800" height="1000" loading="lazy" class="h-[280px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]">
                            @else
                                <div class="flex h-[280px] w-full items-center justify-center pb-8">
                                    <span class="grid h-24 w-24 place-items-center rounded-full bg-ink text-[24px] font-semibold text-primary-foreground">{{ $doctor->initials() }}</span>
                                </div>
                            @endif
                            <span class="absolute bottom-4 left-4 rounded-full bg-card/95 px-3.5 py-1.5 text-[12px] font-medium text-ink backdrop-blur-sm">Next: {{ $doctor->nextAvailableLabel() }}</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h2 class="font-display text-[16px] font-medium text-ink">{{ $doctor->name }}</h2>
                                    <p class="mt-0.5 text-[12px] text-teal-deep">{{ $doctor->specialization ?? 'General practice' }}</p>
                                </div>
                                @if ($doctor->rating)
                                    <div class="shrink-0 flex items-center gap-1 text-[13px] font-medium text-ink">
                                        <svg class="h-3.5 w-3.5 fill-teal-deep text-teal-deep" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M12 2l2.9 6.26L21 9.27l-4.5 4.38L17.8 21 12 17.77 6.2 21l1.3-7.35L3 9.27l6.1-1.01L12 2z"/></svg>
                                        {{ number_format((float) $doctor->rating, 1) }}
                                    </div>
                                @endif
                            </div>
                            <p class="mt-4 flex items-center gap-1.5 text-[12px] text-ink-soft">
                                <svg class="h-3.5 w-3.5 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span class="truncate">{{ $clinicNames ?: 'Not currently affiliated with a clinic' }}</span>
                            </p>
                            <div class="mt-2 flex items-center justify-between gap-4">
                                <span class="text-[12px] text-ink-soft">{{ $doctor->consultationModeLabel() }}</span>
                                @if ($doctor->isPriced())
                                    <span class="text-[13px] font-medium text-ink">From LKR {{ number_format($doctor->consultation_fee) }} <span class="font-normal text-ink-soft">(excl. clinic fees)</span></span>
                                @else
                                    <span class="text-[12px] text-ink-soft">Contact for pricing</span>
                                @endif
                            </div>
                            <div class="mt-5 flex items-center gap-2">
                                <a href="{{ route('doctors.show', $doctor) }}" class="flex-1 rounded-full bg-ink px-5 py-3.5 text-center text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">{{ $doctor->activeAffiliations->isNotEmpty() ? 'Book appointment' : 'View profile' }}</a>
                                <a href="{{ route('doctors.show', $doctor) }}" aria-label="View {{ $doctor->name }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-secondary text-ink"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="col-span-full rounded-3xl bg-card p-8 text-[14px] text-ink-soft">No clinicians are listed yet. Check back soon.</p>
                @endforelse
            </div>
            <p id="empty-results" hidden class="mt-10 rounded-3xl bg-card p-8 text-[14px] text-ink-soft">No clinicians match that search yet. Try a different specialty.</p>
        </main>

        <x-site-footer />
    </div>

    <script>
        (() => {
            const query = document.getElementById('query-filter');
            const cityFilter = document.getElementById('city-filter');
            const dateFilter = document.getElementById('date-filter');
            const dateFilterClear = document.getElementById('date-filter-clear');
            const cards = [...document.querySelectorAll('[data-doctor]')];
            const buttons = [...document.querySelectorAll('button[data-specialty]')];
            let specialty = 'All';
            const update = () => {
                const term = query.value.trim().toLowerCase();
                const city = cityFilter.value;
                const date = dateFilter.value;
                dateFilterClear.hidden = !date;
                let count = 0;
                cards.forEach((card) => {
                    const cities = card.dataset.cities ? card.dataset.cities.split('|') : [];
                    const availableDates = card.dataset.availableDates ? card.dataset.availableDates.split('|') : [];
                    const matchesSpecialty = specialty === 'All' || card.dataset.specialty === specialty;
                    const matchesSearch = !term || card.dataset.search.includes(term);
                    const matchesCity = city === 'All' || cities.includes(city);
                    const matchesDate = !date || availableDates.length === 0 || availableDates.includes(date);
                    const visible = matchesSpecialty && matchesSearch && matchesCity && matchesDate;
                    card.hidden = !visible;
                    if (visible) count++;
                });
                buttons.forEach((button) => button.className = button.dataset.specialty === specialty ? 'rounded-full bg-ink px-4 py-2 text-[12px] font-medium text-primary-foreground' : 'rounded-full bg-secondary px-4 py-2 text-[12px] text-ink-soft transition-colors hover:text-ink');
                document.getElementById('results-count').textContent = `${count} clinician${count === 1 ? '' : 's'} available`;
                document.getElementById('empty-results').hidden = count !== 0 || cards.length === 0;
            };
            document.getElementById('doctor-filters').addEventListener('submit', (event) => { event.preventDefault(); update(); });
            query.addEventListener('input', update);
            cityFilter.addEventListener('change', update);
            dateFilter.addEventListener('change', update);
            dateFilterClear.addEventListener('click', () => { dateFilter.value = ''; update(); });
            buttons.forEach((button) => button.addEventListener('click', () => { specialty = button.dataset.specialty; update(); }));
            update();
        })();
    </script>
</body>
</html>
