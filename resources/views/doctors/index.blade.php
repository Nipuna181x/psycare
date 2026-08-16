<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find & Book a Doctor — PsyCare Sri Lanka</title>
    <meta name="description" content="Search every registered psychiatrist, psychologist and counsellor in Sri Lanka by location, date and specialty, then book an appointment in minutes.">
    <meta property="og:title" content="Find & Book a Doctor — PsyCare Sri Lanka">
    <meta property="og:description" content="Filter clinicians by nearest city, available date and specialty, and reserve the next open slot.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $doctors = [
            ['Dr. Anusha Perera', 'Consultant Psychiatrist', 'Psychiatry', 'Serene Mind Clinic', 'Colombo', 'doc-1.jpg', '4.9', '980+ reviews', 'LKR 4,500', 'Today, 4:00 PM', 'In-person & online'],
            ['Dr. S. Rajaratnam', 'Clinical Psychologist · Trauma', 'Trauma', 'Northern Wellbeing Centre', 'Jaffna', 'doc-2.jpg', '5.0', '640+ reviews', 'LKR 3,800', 'Tuesday, 9:30 AM', 'In-person'],
            ['Ms. Dilani Fernando', 'Counselling Psychologist · Teens', 'Child & teen', 'Lagoon Counselling Rooms', 'Negombo', 'doc-3.jpg', '4.8', '1,120+ reviews', 'LKR 3,200', 'Today, 6:15 PM', 'Online'],
            ['Dr. Nuwan Bandara', 'Consultant Psychiatrist · Addiction', 'Psychiatry', 'Hill Country Medical Institute', 'Kandy', 'doc-4.jpg', '4.7', '520+ reviews', 'LKR 4,000', 'Tomorrow, 11:00 AM', 'In-person & online'],
            ['Ms. Hasini Jayawardena', 'Counselling Psychologist · Couples', 'Counselling', 'Southern Care Collective', 'Galle', 'doc-5.jpg', '4.9', '760+ reviews', 'LKR 3,500', 'Today, 7:30 PM', 'Online'],
            ['Dr. Mahesh Kulasooriya', 'Clinical Psychologist · Anxiety', 'Counselling', 'Eastern Mind Practice', 'Batticaloa', 'doc-6.jpg', '4.8', '410+ reviews', 'LKR 3,600', 'Wednesday, 2:45 PM', 'In-person'],
        ];
    @endphp
    <div class="min-h-screen bg-background text-ink selection:bg-teal/20">
        <nav class="mx-auto flex max-w-[1320px] items-center justify-between gap-4 px-5 py-6 md:px-9 md:py-7">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink"><span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span><span class="font-display text-lg font-medium tracking-tight">PsyCare</span></a>
            <div class="hidden items-center gap-1 rounded-full bg-card px-2 py-1.5 shadow-[0_1px_0_0_var(--border)] lg:flex"><a href="{{ route('home') }}" class="rounded-full px-4 py-2 text-[13px] text-ink-soft transition-colors hover:text-ink">Home</a><a href="{{ route('doctors.index') }}" class="rounded-full bg-secondary px-4 py-2 text-[13px] text-ink">Doctors</a></div>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Log in <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
        </nav>

        <main class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
            <header class="max-w-[38ch]"><p class="eyebrow">Appointments</p><h1 class="display-head mt-3 text-[clamp(2.1rem,4.6vw,3.6rem)] text-ink">Book any doctor, any clinic, one calm search</h1></header>
            <form id="doctor-filters" class="mt-10 rounded-3xl bg-card p-4 md:p-5">
                <div class="grid gap-3 md:grid-cols-[1.1fr_1fr_1.3fr_auto]">
                    <label class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5"><svg class="h-4 w-4 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><span class="sr-only">Nearest location</span><select id="city-filter" class="w-full bg-transparent text-[13px] text-ink outline-none">@foreach (['All locations', 'Colombo', 'Kandy', 'Galle', 'Jaffna', 'Negombo', 'Batticaloa'] as $city)<option>{{ $city }}</option>@endforeach</select></label>
                    <label class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5"><svg class="h-4 w-4 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg><span class="sr-only">Preferred date</span><input id="date-filter" type="date" class="w-full bg-transparent text-[13px] text-ink outline-none"></label>
                    <label class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3.5"><svg class="h-4 w-4 shrink-0 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg><span class="sr-only">Doctor or clinic</span><input id="query-filter" placeholder="Doctor, clinic or concern" class="w-full bg-transparent text-[13px] text-ink placeholder:text-muted-foreground outline-none"></label>
                    <button type="submit" class="rounded-2xl bg-ink px-7 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Search</button>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">@foreach (['All', 'Psychiatry', 'Counselling', 'Trauma', 'Child & teen'] as $specialty)<button type="button" data-specialty="{{ $specialty }}" class="rounded-full px-4 py-2 text-[12px]">{{ $specialty }}</button>@endforeach</div>
            </form>

            <p id="results-count" class="mt-8 text-[13px] text-ink-soft"></p>
            <div id="doctor-grid" class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($doctors as [$name, $role, $specialty, $clinic, $city, $image, $rating, $reviews, $fee, $next, $mode])
                    <article data-doctor data-city="{{ $city }}" data-specialty="{{ $specialty }}" data-search="{{ Str::lower($name.' '.$clinic.' '.$role) }}" class="group overflow-hidden rounded-3xl bg-card">
                        <div class="relative overflow-hidden"><img src="{{ Vite::asset('resources/images/psycare/'.$image) }}" alt="{{ $name }}, {{ $role }}" width="800" height="1000" loading="lazy" class="h-[320px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]"><span class="absolute bottom-4 left-4 rounded-full bg-card/95 px-3.5 py-1.5 text-[12px] font-medium text-ink backdrop-blur-sm">Next: {{ $next }}</span></div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4"><div class="min-w-0"><h2 class="font-display text-[16px] font-medium text-ink">{{ $name }}</h2><p class="mt-0.5 text-[12px] text-muted-foreground">{{ $role }}</p></div><p class="flex shrink-0 items-center gap-1 text-[13px] text-ink"><svg class="h-3.5 w-3.5 fill-teal text-teal" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.12 2.12 0 0 0 1.595 1.16l5.166.75a.53.53 0 0 1 .294.904l-3.738 3.644a2.12 2.12 0 0 0-.609 1.878l.882 5.146a.53.53 0 0 1-.77.559l-4.62-2.429a2.12 2.12 0 0 0-1.97 0l-4.62 2.429a.53.53 0 0 1-.77-.559l.882-5.146a2.12 2.12 0 0 0-.609-1.878L2.16 9.788a.53.53 0 0 1 .294-.904l5.166-.75a2.12 2.12 0 0 0 1.595-1.16z"/></svg>{{ $rating }}</p></div>
                            <dl class="mt-4 space-y-1.5 text-[12px] text-ink-soft"><div class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><dd>{{ $clinic }} · {{ $city }}</dd></div><div class="flex items-center justify-between"><dd>{{ $mode }}</dd><dd class="text-ink">{{ $fee }}</dd></div></dl>
                            <div class="mt-5 flex items-center gap-2"><button type="button" class="flex-1 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Book appointment</button><span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-secondary text-ink"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></span></div>
                        </div>
                    </article>
                @endforeach
            </div>
            <p id="empty-results" hidden class="mt-10 rounded-3xl bg-card p-8 text-[14px] text-ink-soft">No clinicians match those filters yet. Try a wider location or clear the specialty.</p>
        </main>

        <footer id="contact" class="px-3 pb-3 md:px-5 md:pb-5"><div class="rounded-3xl bg-ink px-6 py-14 text-primary-foreground md:px-12 md:py-20"><div class="grid gap-12 lg:grid-cols-[1.2fr_1.8fr]"><div><p class="font-display text-2xl font-medium tracking-tight">PsyCare</p><p class="mt-4 max-w-[34ch] text-[15px] leading-relaxed text-primary-foreground/65">One place to find and book every registered mental health professional in Sri Lanka.</p><a href="#doctor-grid" class="mt-8 inline-flex items-center gap-2 rounded-full bg-card px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Book a doctor <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a></div><div class="grid gap-10 sm:grid-cols-3">@foreach ([['Care', ['Individual therapy', 'Psychiatry & medication', 'Group circles', 'Child & teen care']], ['Company', ['About PsyCare', 'For clinicians', 'Careers', 'Press']], ['Trust', ['Privacy & data', 'Confidentiality policy', 'care@psycare.lk', '+94 11 244 0000']]] as [$title, $links])<div><p class="text-[11px] tracking-[0.16em] text-primary-foreground/45 uppercase">{{ $title }}</p><ul class="mt-5 space-y-2.5 text-[14px] text-primary-foreground/75">@foreach ($links as $link)<li class="transition-colors hover:text-primary-foreground">{{ $link }}</li>@endforeach</ul></div>@endforeach</div></div><div class="mt-16 flex flex-wrap items-center justify-between gap-4 border-t border-primary-foreground/15 pt-6 text-[11px] tracking-[0.14em] text-primary-foreground/45 uppercase"><span>© 2026 PsyCare Lanka (Pvt) Ltd</span><span>In crisis? Call 1926 — free, 24 hours</span></div></div></footer>
    </div>

    <script>
        (() => {
            const city = document.getElementById('city-filter');
            const date = document.getElementById('date-filter');
            const query = document.getElementById('query-filter');
            const cards = [...document.querySelectorAll('[data-doctor]')];
            const buttons = [...document.querySelectorAll('button[data-specialty]')];
            let specialty = 'All';
            const update = () => {
                const term = query.value.trim().toLowerCase();
                let count = 0;
                cards.forEach((card) => {
                    const visible = (city.value === 'All locations' || card.dataset.city === city.value) && (specialty === 'All' || card.dataset.specialty === specialty) && (!term || card.dataset.search.includes(term));
                    card.hidden = !visible;
                    if (visible) count++;
                });
                buttons.forEach((button) => button.className = button.dataset.specialty === specialty ? 'rounded-full bg-ink px-4 py-2 text-[12px] font-medium text-primary-foreground' : 'rounded-full bg-secondary px-4 py-2 text-[12px] text-ink-soft transition-colors hover:text-ink');
                document.getElementById('results-count').textContent = `${count} clinician${count === 1 ? '' : 's'} available${city.value === 'All locations' ? ' island-wide' : ` in ${city.value}`}${date.value ? ` · from ${date.value}` : ''}`;
                document.getElementById('empty-results').hidden = count !== 0;
            };
            document.getElementById('doctor-filters').addEventListener('submit', (event) => { event.preventDefault(); update(); });
            [city, date, query].forEach((field) => field.addEventListener('input', update));
            buttons.forEach((button) => button.addEventListener('click', () => { specialty = button.dataset.specialty; update(); }));
            update();
        })();
    </script>
</body>
</html>
