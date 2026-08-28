<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Appointments — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />

        <main class="mx-auto max-w-[1000px] px-5 pb-24 md:px-9">
            <header><p class="eyebrow">Appointments</p><h1 class="display-head mt-2 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">My appointments</h1></header>

            <section class="mt-8">
                <h2 class="font-display text-[15px] font-medium text-ink">Upcoming</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($upcoming as $appointment)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-card p-5">
                            <div class="flex items-center gap-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-ink text-[13px] font-semibold text-primary-foreground">{{ $appointment->doctor->initials() }}</span>
                                <div>
                                    <p class="text-[14px] font-medium text-ink">{{ $appointment->doctor->name }}</p>
                                    <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->medicalCenter->name }}</p>
                                    <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }} · {{ $appointment->mode === 'online' ? 'Online' : 'In person' }}</p>
                                </div>
                            </div>
                            <x-dashboard.badge :status="$appointment->status" />
                        </div>
                    @empty
                        <p class="rounded-3xl bg-card p-8 text-[13px] text-ink-soft">No upcoming appointments yet. <a href="{{ route('doctors.index') }}" class="font-medium text-ink underline">Book a doctor</a>.</p>
                    @endforelse
                </div>
            </section>

            <section class="mt-10">
                <h2 class="font-display text-[15px] font-medium text-ink">Past</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($past as $appointment)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-card p-5 opacity-80">
                            <div class="flex items-center gap-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-secondary text-[13px] font-semibold text-ink">{{ $appointment->doctor->initials() }}</span>
                                <div>
                                    <p class="text-[14px] font-medium text-ink">{{ $appointment->doctor->name }}</p>
                                    <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                                </div>
                            </div>
                            <x-dashboard.badge :status="$appointment->status" />
                        </div>
                    @empty
                        <p class="rounded-3xl bg-card p-8 text-[13px] text-ink-soft">No past appointments yet.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
