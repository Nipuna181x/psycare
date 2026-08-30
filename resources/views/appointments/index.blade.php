<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Appointments — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @include('partials.favicon')

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
                        <details class="group rounded-3xl bg-card p-5">
                            <summary class="flex flex-wrap cursor-pointer list-none items-center justify-between gap-4 marker:content-none">
                                <div class="flex items-center gap-4">
                                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-ink text-[13px] font-semibold text-primary-foreground">{{ $appointment->doctor->initials() }}</span>
                                    <div>
                                        <p class="text-[14px] font-medium text-ink">{{ $appointment->doctor->name }}</p>
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->medicalCenter->name }}</p>
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                                        @if ($appointment->payment?->status === 'succeeded')
                                            <p class="mt-1 text-[10px] font-semibold tracking-[0.06em] text-blue-700 uppercase">Payment ID {{ $appointment->payment->reference() }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($appointment->payment?->status === 'succeeded')
                                        <a href="{{ route('payments.receipt.download', $appointment->payment) }}" class="rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-[10px] font-semibold tracking-[0.06em] text-blue-700 uppercase transition-colors hover:bg-blue-100">Download receipt</a>
                                    @endif
                                    <x-dashboard.badge :status="$appointment->status" />
                                    <svg class="h-4 w-4 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </summary>

                            <div class="mt-5 grid gap-4 border-t border-border pt-5 sm:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">Clinic</p>
                                    <p class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->medicalCenter->name }}</p>
                                    @if ($appointment->medicalCenter->address)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->medicalCenter->address }}</p>
                                    @endif
                                    @if ($appointment->medicalCenter->phone)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->medicalCenter->phone }}</p>
                                    @endif
                                    @if ($appointment->medicalCenter->address)
                                        <a
                                            href="https://www.google.com/maps/search/?api=1&query={{ urlencode($appointment->medicalCenter->address) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-border px-4 py-2 text-[10px] font-semibold tracking-[0.06em] text-ink uppercase transition-colors hover:bg-secondary"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                            Find in Google Maps
                                        </a>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">Appointment</p>
                                    <p class="mt-1 text-[12px] text-ink-soft">Mode: <span class="font-medium text-ink">{{ ucfirst($appointment->mode) }}</span></p>
                                    @if ($appointment->reason)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">Reason: <span class="font-medium text-ink">{{ $appointment->reason }}</span></p>
                                    @endif
                                    @if ($appointment->consultation_fee)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">Fee: <span class="font-medium text-ink">LKR {{ number_format($appointment->consultation_fee, 2) }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </details>
                    @empty
                        <p class="rounded-3xl bg-card p-8 text-[13px] text-ink-soft">No upcoming appointments yet. <a href="{{ route('doctors.index') }}" class="font-medium text-ink underline">Book a doctor</a>.</p>
                    @endforelse
                </div>
            </section>

            <section class="mt-10">
                <h2 class="font-display text-[15px] font-medium text-ink">Past</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($past as $appointment)
                        <details class="group rounded-3xl bg-card p-5 opacity-80">
                            <summary class="flex flex-wrap cursor-pointer list-none items-center justify-between gap-4 marker:content-none">
                                <div class="flex items-center gap-4">
                                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-secondary text-[13px] font-semibold text-ink">{{ $appointment->doctor->initials() }}</span>
                                    <div>
                                        <p class="text-[14px] font-medium text-ink">{{ $appointment->doctor->name }}</p>
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                                        @if ($appointment->payment?->status === 'succeeded')
                                            <p class="mt-1 text-[10px] font-semibold tracking-[0.06em] text-blue-700 uppercase">Payment ID {{ $appointment->payment->reference() }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($appointment->payment?->status === 'succeeded')
                                        <a href="{{ route('payments.receipt.download', $appointment->payment) }}" class="rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-[10px] font-semibold tracking-[0.06em] text-blue-700 uppercase transition-colors hover:bg-blue-100">Download receipt</a>
                                    @endif
                                    <x-dashboard.badge :status="$appointment->status" />
                                    <svg class="h-4 w-4 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </summary>

                            <div class="mt-5 grid gap-4 border-t border-border pt-5 sm:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">Clinic</p>
                                    <p class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->medicalCenter->name }}</p>
                                    @if ($appointment->medicalCenter->address)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->medicalCenter->address }}</p>
                                    @endif
                                    @if ($appointment->medicalCenter->phone)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $appointment->medicalCenter->phone }}</p>
                                    @endif
                                    @if ($appointment->medicalCenter->address)
                                        <a
                                            href="https://www.google.com/maps/search/?api=1&query={{ urlencode($appointment->medicalCenter->address) }}"
                                            target="_blank"
                                            rel="noopener"
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-border px-4 py-2 text-[10px] font-semibold tracking-[0.06em] text-ink uppercase transition-colors hover:bg-secondary"
                                        >
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                            Find in Google Maps
                                        </a>
                                    @endif
                                </div>

                                <div>
                                    <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">Appointment</p>
                                    <p class="mt-1 text-[12px] text-ink-soft">Mode: <span class="font-medium text-ink">{{ ucfirst($appointment->mode) }}</span></p>
                                    @if ($appointment->reason)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">Reason: <span class="font-medium text-ink">{{ $appointment->reason }}</span></p>
                                    @endif
                                    @if ($appointment->consultation_fee)
                                        <p class="mt-0.5 text-[12px] text-ink-soft">Fee: <span class="font-medium text-ink">LKR {{ number_format($appointment->consultation_fee, 2) }}</span></p>
                                    @endif
                                </div>
                            </div>
                        </details>
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
