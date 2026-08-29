<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PsyCare — Book Any Mental Health Doctor in Sri Lanka</title>
    <meta name="description" content="PsyCare is Sri Lanka's single booking platform for registered psychiatrists, psychologists and counsellors, with AI voice intake, pre-booking assessments and group therapy.">
    <meta name="author" content="PsyCare Sri Lanka">
    <meta property="og:title" content="PsyCare — Book Any Mental Health Doctor in Sri Lanka">
    <meta property="og:description" content="Every registered clinician in one place: AI voice intake, pre-booking assessments and moderated group therapy sessions.">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink selection:bg-teal/20">
        <section id="top" class="px-3 pt-3 md:px-5 md:pt-5">
            <div class="relative overflow-hidden rounded-3xl bg-ink">
                <img src="{{ Vite::asset('resources/images/psycare/hero-consult.jpg') }}" alt="A psychiatrist listening to a patient in a sunlit consulting room in Sri Lanka" width="1920" height="1200" class="h-[720px] w-full object-cover object-center md:h-[780px]">
                <div class="absolute inset-0 bg-gradient-to-t from-ink/85 via-ink/30 to-ink/55"></div>

                <nav class="absolute inset-x-0 top-0 z-20 flex items-center justify-between gap-4 px-5 py-5 md:px-9 md:py-7">
                    <a href="#top" class="flex items-center gap-2.5 text-primary-foreground">
                        <span class="grid h-8 w-8 place-items-center rounded-full bg-primary-foreground/20 backdrop-blur-sm"><span class="h-2.5 w-2.5 rounded-full bg-primary-foreground"></span></span>
                        <span class="font-display text-lg font-medium tracking-tight">PsyCare</span>
                    </a>
                    <div class="hidden items-center gap-1 rounded-full bg-primary-foreground/12 px-2 py-1.5 backdrop-blur-md lg:flex">
                        @foreach ([['Home', '/'], ['Book a Doctor', '/doctors'], ['Lumi', '/ai-companion'], ['My Health Records', '/health-records'], ['My Appointments', '/appointments'], ['Group Therapy', route('therapy-rooms.index')], ['Mood Tracker', '/mood-tracker']] as [$label, $href])
                            <a href="{{ $href }}" class="whitespace-nowrap rounded-full px-3 py-2 text-[12px] text-primary-foreground/80 transition-colors hover:bg-primary-foreground/20 hover:text-primary-foreground">{{ $label }}</a>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2">
                        <x-home-account-control />
                        <button type="button" aria-label="Open menu" class="grid h-11 w-11 place-items-center rounded-full bg-primary-foreground/15 text-primary-foreground backdrop-blur-md lg:hidden">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 12h16M4 6h16M4 18h16"/></svg>
                        </button>
                    </div>
                </nav>

                <div class="absolute inset-x-0 bottom-0 px-5 pb-6 md:px-9 md:pb-9">
                    <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
                        <div class="rise-in">
                            <h1 class="display-head max-w-[16ch] text-[clamp(2.4rem,5.6vw,4.4rem)] text-primary-foreground">Every doctor in Sri Lanka, one calm booking.</h1>
                            <div class="mt-7 flex flex-wrap gap-2">
                                @foreach (['Psychiatry', 'Counselling', 'Group therapy', 'AI voice intake', 'Child & teen', 'Online consults'] as $chip)
                                    <span class="{{ $loop->first ? 'rounded-full bg-card px-4 py-2 text-[12px] font-medium text-ink' : 'rounded-full bg-primary-foreground/14 px-4 py-2 text-[12px] text-primary-foreground/85 backdrop-blur-md' }}">{{ $chip }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex flex-col gap-6">
                            <p class="max-w-[44ch] text-[15px] leading-relaxed text-primary-foreground/80">PsyCare brings every registered psychiatrist, psychologist and counsellor on the island into one place — with an AI voice assistant that prepares your assessment before the appointment begins.</p>
                            <div class="flex items-center gap-4 text-primary-foreground/70">
                                <button type="button" aria-label="Previous" class="hover:text-primary-foreground"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>
                                <span class="text-[12px] tracking-widest">01</span>
                                <span class="h-px flex-1 bg-primary-foreground/25"><span class="block h-px w-1/5 bg-primary-foreground"></span></span>
                                <span class="text-[12px] tracking-widest">05</span>
                                <button type="button" aria-label="Next" class="hover:text-primary-foreground"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>
                            </div>
                            <article class="flex items-center gap-4 rounded-2xl bg-card p-3.5">
                                <img src="{{ Vite::asset('resources/images/psycare/doc-1.jpg') }}" alt="Dr. Anusha Perera, consultant psychiatrist" width="800" height="1000" loading="lazy" class="h-16 w-16 shrink-0 rounded-xl object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-display text-[15px] font-medium text-ink">Dr. Anusha Perera</p>
                                    <p class="text-[12px] text-muted-foreground">Consultant Psychiatrist · Colombo 07</p>
                                    <p class="mt-1.5 flex items-center gap-1.5 text-[12px] text-ink-soft"><svg class="h-3 w-3 fill-teal text-teal" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.12 2.12 0 0 0 1.595 1.16l5.166.75a.53.53 0 0 1 .294.904l-3.738 3.644a2.12 2.12 0 0 0-.609 1.878l.882 5.146a.53.53 0 0 1-.77.559l-4.62-2.429a2.12 2.12 0 0 0-1.97 0l-4.62 2.429a.53.53 0 0 1-.77-.559l.882-5.146a2.12 2.12 0 0 0-.609-1.878L2.16 9.788a.53.53 0 0 1 .294-.904l5.166-.75a2.12 2.12 0 0 0 1.595-1.16z"/></svg>14 years experience (4.9 rating)</p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <main>
            <section class="mx-auto max-w-[1320px] px-5 py-14 md:px-9 md:py-20">
                <div class="grid gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([['1,480', 'Registered clinicians'], ['62K', 'Appointments booked'], ['4.9', 'Patient rating'], ['9 min', 'Average intake time']] as [$value, $label])
                        <div class="border-l border-border pl-6 first:border-l-0 first:pl-0"><p class="font-display text-[clamp(2rem,3.4vw,2.8rem)] font-medium text-ink">{{ $value }}</p><p class="mt-1 text-[11px] tracking-[0.14em] text-muted-foreground uppercase">{{ $label }}</p></div>
                    @endforeach
                </div>
            </section>

            <section id="services" class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
                <div class="grid gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-start">
                    <div>
                        <div class="flex items-baseline gap-3"><span class="eyebrow shrink-0">Our services /</span><h2 class="display-head text-[clamp(1.9rem,3.6vw,3rem)] text-ink">Discover our signature mental health services</h2></div>
                        <div class="mt-10 flex items-center gap-4">
                            <div class="flex -space-x-3">
                                @foreach (['doc-1.jpg', 'doc-2.jpg', 'doc-3.jpg'] as $doctorImage)
                                    <img src="{{ Vite::asset('resources/images/psycare/'.$doctorImage) }}" alt="" width="800" height="1000" loading="lazy" class="h-10 w-10 rounded-full border-2 border-background object-cover">
                                @endforeach
                            </div>
                            <div><p class="font-display text-xl font-medium text-ink">7,500+</p><p class="text-[12px] text-muted-foreground">Verified patient reviews</p></div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-8">
                        <p class="max-w-[52ch] text-[15px] leading-relaxed text-ink-soft">Care delivered with privacy, precision and patience. From the first quiet conversation to the follow-up review, PsyCare keeps one continuous record so you never have to explain yourself twice.</p>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach ([['AI Voice Intake', 'service-voice.jpg', 'Speak naturally; the assistant asks what a clinician would ask first.'], ['Pre-Booking Assessment', 'service-assessment.jpg', 'A structured clinical note reaches your doctor before you arrive.'], ['Group Therapy', 'service-group.jpg', 'Small moderated circles of ten, island-wide and online.']] as [$title, $image, $body])
                                <article class="group relative overflow-hidden rounded-2xl bg-ink">
                                    <img src="{{ Vite::asset('resources/images/psycare/'.$image) }}" alt="{{ $title }}" width="900" height="1100" loading="lazy" class="h-80 w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-gradient-to-t from-ink/95 via-ink/35 to-transparent"></div>
                                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-4">
                                        <div class="min-w-0"><h3 class="font-display text-[15px] font-medium text-primary-foreground uppercase">{{ $title }}</h3><p class="mt-1.5 text-[12px] leading-snug text-primary-foreground/70">{{ $body }}</p></div>
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-card text-ink"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="about" class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
                <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <img src="{{ Vite::asset('resources/images/psycare/about-care.jpg') }}" alt="A counsellor speaking with a young patient and her mother in a bright room" width="1200" height="900" loading="lazy" class="h-[380px] w-full rounded-3xl object-cover md:h-[480px]">
                    <div>
                        <div class="flex items-baseline gap-3"><span class="eyebrow shrink-0">About PsyCare /</span><h2 class="display-head text-[clamp(1.9rem,3.6vw,3rem)] text-ink">Excellence in mental health care, with comfort at the centre</h2></div>
                        <p class="mt-6 max-w-[54ch] text-[15px] leading-relaxed text-ink-soft">PsyCare was built with Sri Lankan clinicians, for Sri Lankan patients. Every practitioner is verified against their professional register, and every appointment begins with a doctor who already understands why you came.</p>
                        <ul class="mt-9 divide-y divide-border border-y border-border">
                            @foreach (['One island-wide directory, real availability', 'Assessment summary sent ahead of your visit', 'Confidential records, encrypted and deletable', 'Follow-up reminders and medication reviews'] as $item)
                                <li class="flex items-center justify-between gap-4 py-4"><span class="text-[15px] text-ink">{{ $item }}</span><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-secondary text-ink-soft"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14M12 5v14"/></svg></span></li>
                            @endforeach
                        </ul>
                        <a href="#doctors" class="mt-9 inline-flex rounded-full bg-ink px-7 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Book an appointment</a>
                    </div>
                </div>
            </section>

            <section id="doctors" class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <h2 class="display-head max-w-[22ch] text-[clamp(1.9rem,3.6vw,3rem)] text-ink">Meet the professionals behind your recovery</h2>
                    <a href="{{ route('doctors.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-ink-soft transition-colors hover:text-teal-deep">Browse all 1,480 clinicians <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
                </div>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([['Dr. Anusha Perera', 'Consultant Psychiatrist', 'Serene Mind Clinic', 'Colombo', 'doc-1.jpg', '4.9', 'LKR 4,500', 'Today, 4:00 PM', 'In-person & online'], ['Dr. S. Rajaratnam', 'Clinical Psychologist · Trauma', 'Northern Wellbeing Centre', 'Jaffna', 'doc-2.jpg', '5.0', 'LKR 3,800', 'Tuesday, 9:30 AM', 'In-person'], ['Ms. Dilani Fernando', 'Counselling Psychologist · Teens', 'Lagoon Counselling Rooms', 'Negombo', 'doc-3.jpg', '4.8', 'LKR 3,200', 'Today, 6:15 PM', 'Online']] as [$name, $role, $clinic, $city, $image, $rating, $fee, $next, $mode])
                        <article class="group flex h-full flex-col overflow-hidden rounded-3xl bg-card">
                            <div class="relative overflow-hidden">
                                <img src="{{ Vite::asset('resources/images/psycare/'.$image) }}" alt="{{ $name }}, {{ $role }}" width="800" height="1000" loading="lazy" class="h-[320px] w-full object-cover transition-transform duration-700 group-hover:scale-[1.04]">
                                <span class="absolute bottom-4 left-4 rounded-full bg-card/95 px-3.5 py-1.5 text-[12px] font-medium text-ink backdrop-blur-sm">Next: {{ $next }}</span>
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0"><h3 class="font-display text-[16px] font-medium text-ink">{{ $name }}</h3><p class="mt-0.5 text-[12px] text-muted-foreground">{{ $role }}</p></div>
                                    <p class="flex shrink-0 items-center gap-1 text-[13px] text-ink"><svg class="h-3.5 w-3.5 fill-teal text-teal" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.12 2.12 0 0 0 1.595 1.16l5.166.75a.53.53 0 0 1 .294.904l-3.738 3.644a2.12 2.12 0 0 0-.609 1.878l.882 5.146a.53.53 0 0 1-.77.559l-4.62-2.429a2.12 2.12 0 0 0-1.97 0l-4.62 2.429a.53.53 0 0 1-.77-.559l.882-5.146a2.12 2.12 0 0 0-.609-1.878L2.16 9.788a.53.53 0 0 1 .294-.904l5.166-.75a2.12 2.12 0 0 0 1.595-1.16z"/></svg>{{ $rating }}</p>
                                </div>
                                <dl class="mt-4 space-y-1.5 text-[12px] text-ink-soft">
                                    <div class="flex items-center gap-1.5"><svg class="h-3.5 w-3.5 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><dd>{{ $clinic }} · {{ $city }}</dd></div>
                                    <div class="flex items-center justify-between"><dd>{{ $mode }}</dd><dd class="text-ink">{{ $fee }}</dd></div>
                                </dl>
                                <div class="mt-auto flex items-center gap-2 pt-5"><a href="{{ route('login') }}" class="flex-1 rounded-full bg-ink px-5 py-3 text-center text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Book appointment</a><a href="{{ route('login') }}" aria-label="Book an appointment with {{ $name }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-secondary text-ink transition-transform hover:-translate-y-0.5"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="reviews" class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
                <div class="rounded-3xl bg-secondary px-6 py-16 text-center md:px-16 md:py-24">
                    <blockquote class="mx-auto max-w-[52ch] font-display text-[clamp(1.15rem,2.1vw,1.6rem)] leading-snug font-medium tracking-tight text-ink uppercase">“From the first call, I did not have to repeat my story. The assistant listened, my doctor had already read it, and the session felt like continuing a conversation rather than starting one.”</blockquote>
                    <div class="mt-10 flex items-center justify-center gap-3"><img src="{{ Vite::asset('resources/images/psycare/doc-3.jpg') }}" alt="Nimasha Gunawardena" width="800" height="1000" loading="lazy" class="h-10 w-10 rounded-full object-cover"><div class="text-left"><p class="text-[13px] font-medium text-ink">Nimasha Gunawardena</p><p class="text-[12px] text-muted-foreground">Patient · Negombo</p></div></div>
                    <div class="mt-9 flex items-center justify-center gap-2"><span class="h-1.5 w-6 rounded-full bg-ink"></span><span class="h-1.5 w-1.5 rounded-full bg-ink/25"></span><span class="h-1.5 w-1.5 rounded-full bg-ink/25"></span></div>
                </div>
            </section>
        </main>

        <footer id="contact" class="px-3 pb-3 md:px-5 md:pb-5">
            <div class="rounded-3xl bg-ink px-6 py-14 text-primary-foreground md:px-12 md:py-20">
                <div class="grid gap-12 lg:grid-cols-[1.2fr_1.8fr]">
                    <div><p class="font-display text-2xl font-medium tracking-tight">PsyCare</p><p class="mt-4 max-w-[34ch] text-[15px] leading-relaxed text-primary-foreground/65">One place to find and book every registered mental health professional in Sri Lanka.</p><a href="#doctors" class="mt-8 inline-flex items-center gap-2 rounded-full bg-card px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Book a doctor <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a></div>
                    <div class="grid gap-10 sm:grid-cols-3">
                        @foreach ([['Care', ['Individual therapy', 'Psychiatry & medication', 'Group circles', 'Child & teen care']], ['Company', ['About PsyCare', 'For clinicians', 'Careers', 'Press']], ['Trust', ['Privacy & data', 'Confidentiality policy', 'care@psycare.lk', '+94 11 244 0000']]] as [$title, $links])
                            <div><p class="text-[11px] tracking-[0.16em] text-primary-foreground/45 uppercase">{{ $title }}</p><ul class="mt-5 space-y-2.5 text-[14px] text-primary-foreground/75">@foreach ($links as $link)<li class="transition-colors hover:text-primary-foreground">{{ $link }}</li>@endforeach</ul></div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-16 flex flex-wrap items-center justify-between gap-4 border-t border-primary-foreground/15 pt-6 text-[11px] tracking-[0.14em] text-primary-foreground/45 uppercase"><span>© 2026 PsyCare Lanka (Pvt) Ltd</span><span>In crisis? Call 1926 — free, 24 hours</span></div>
            </div>
        </footer>
    </div>
</body>
</html>
