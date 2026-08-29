<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking confirmed — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />

        <main class="mx-auto max-w-[640px] px-5 pb-24 md:px-9">
            <div class="rounded-3xl bg-card p-8 text-center md:p-10">
                <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-teal/15 text-teal-deep">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <p class="eyebrow mt-5">Booking confirmed</p>
                <h1 class="display-head mt-2 text-[clamp(1.6rem,3.4vw,2.2rem)] text-ink">You're all set, {{ Str::before($appointment->patient_name, ' ') }}</h1>
                <p class="mt-3 text-[13px] leading-relaxed text-ink-soft">Your appointment with {{ $appointment->doctor->name }} is confirmed. A summary has been shared with {{ $appointment->medicalCenter->name }} so they can prepare.</p>

                <div class="mt-8 space-y-3 rounded-2xl bg-secondary p-5 text-left">
                    <div class="flex items-center justify-between text-[13px]"><span class="text-ink-soft">Booking reference</span><span class="font-medium text-ink">#{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</span></div>
                    @if ($appointment->payment?->status === 'succeeded')
                        <div class="flex items-center justify-between text-[13px]"><span class="text-ink-soft">Payment ID</span><span class="font-medium text-ink">{{ $appointment->payment->reference() }}</span></div>
                    @endif
                    <div class="flex items-center justify-between text-[13px]"><span class="text-ink-soft">Doctor</span><span class="font-medium text-ink">{{ $appointment->doctor->name }}</span></div>
                    <div class="flex items-center justify-between text-[13px]"><span class="text-ink-soft">Date & time</span><span class="font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}, {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span></div>
                </div>

                <div class="mt-8 flex flex-col gap-2.5 sm:flex-row">
                    <a href="{{ route('appointments.index') }}" class="flex-1 rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">View my appointments</a>
                    @if ($appointment->payment?->status === 'succeeded')
                        <a href="{{ route('payments.receipt.download', $appointment->payment) }}" class="flex-1 rounded-2xl bg-blue-700 px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-white uppercase transition-transform hover:-translate-y-0.5">Download receipt</a>
                    @endif
                    <a href="{{ route('doctors.index') }}" class="flex-1 rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Back to doctors</a>
                </div>
            </div>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
