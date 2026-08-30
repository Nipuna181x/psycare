<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $doctor->name }} — PsyCare Sri Lanka</title>
    <meta name="description" content="{{ $doctor->name }}, {{ $doctor->specialization ?? 'clinician' }} on PsyCare. Book an appointment online.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink selection:bg-teal/20">
        <x-patient-nav />

        <main class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
            <a href="{{ route('doctors.index') }}" class="inline-flex items-center gap-1.5 text-[12px] text-ink-soft transition-colors hover:text-ink">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to all doctors
            </a>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
                <section class="rounded-3xl bg-card p-6 md:p-8">
                    <div class="flex flex-wrap items-start gap-5">
                        @if ($doctor->avatarUrl())
                            <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" width="160" height="160" class="h-20 w-20 shrink-0 rounded-full object-cover">
                        @else
                            <span class="grid h-20 w-20 shrink-0 place-items-center rounded-full bg-ink text-[22px] font-semibold text-primary-foreground">{{ $doctor->initials() }}</span>
                        @endif
                        <div class="min-w-0">
                            <p class="eyebrow">{{ $doctor->specialization ?? 'General practice' }}</p>
                            <h1 class="display-head mt-1 text-[clamp(1.7rem,3.2vw,2.4rem)] text-ink">{{ $doctor->name }}</h1>
                            <p class="mt-2 flex items-center gap-1.5 text-[13px] text-ink-soft">
                                <svg class="h-3.5 w-3.5 text-teal-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $doctor->bookableAffiliations->pluck('clinic.name')->implode(', ') ?: 'Not currently affiliated with a clinic' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-secondary p-4">
                            <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Experience</p>
                            <p class="mt-1 font-display text-[16px] font-medium text-ink">{{ $doctor->years_of_experience ? $doctor->years_of_experience.'+ years' : 'Not specified' }}</p>
                        </div>
                        <div class="rounded-2xl bg-secondary p-4">
                            <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Consultation fee</p>
                            <p class="mt-1 font-display text-[16px] font-medium text-ink">{{ $doctor->consultation_fee ? 'LKR '.number_format($doctor->consultation_fee) : 'On request' }}</p>
                        </div>
                        <div class="rounded-2xl bg-secondary p-4">
                            <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Consultation modes</p>
                            <p class="mt-1 font-display text-[16px] font-medium text-ink">In-person & online</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="font-display text-[15px] font-medium text-ink">About</h2>
                        <p class="mt-2 max-w-[65ch] text-[14px] leading-relaxed text-ink-soft">{{ $doctor->bio ?? 'This clinician has not added a biography yet. Their clinic can add one from the doctor management portal.' }}</p>
                    </div>

                    <div class="mt-8">
                        <h2 class="font-display text-[15px] font-medium text-ink">What happens after you book</h2>
                        <ol class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach (['Pick a date & time', 'Share your details', 'A short AI voice pre-assessment', 'Review & confirm'] as $step)
                                <li class="flex items-center gap-3 rounded-2xl bg-secondary px-4 py-3 text-[13px] text-ink">
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-ink text-[11px] font-semibold text-primary-foreground">{{ $loop->iteration }}</span>
                                    {{ $step }}
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </section>

                <aside class="sticky top-6 rounded-3xl bg-ink p-6 text-primary-foreground md:p-7">
                    <p class="font-display text-[16px] font-medium">Book with {{ $doctor->name }}</p>
                    @if ($hasActiveAffiliation)
                        <p class="mt-2 text-[13px] leading-relaxed text-primary-foreground/70">Booking takes about 3 minutes, including a short AI-assisted voice pre-assessment so your doctor is prepared before you arrive.</p>
                        <a href="{{ route('booking.clinic', $doctor) }}" class="mt-6 flex items-center justify-center gap-2 rounded-full bg-card px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">
                            Book appointment
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                        </a>
                        @guest('web')
                            <p class="mt-3 text-center text-[11px] text-primary-foreground/50">You'll be asked to log in first.</p>
                        @endguest
                    @else
                        <p class="mt-2 text-[13px] leading-relaxed text-primary-foreground/70">This clinician isn't currently affiliated with a clinic on PsyCare, so online booking isn't available yet.</p>
                        <span class="mt-6 flex items-center justify-center rounded-full bg-primary-foreground/12 px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground/60 uppercase">Not currently accepting bookings</span>
                    @endif
                </aside>
            </div>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
