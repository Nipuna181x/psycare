<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment status — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $copy = match ($state) {
            'cancelled' => ['label' => 'Payment cancelled', 'title' => 'Your slot has been released', 'body' => 'No payment was recorded. You can start a fresh booking whenever you are ready.', 'tone' => 'bg-amber-50 text-amber-700'],
            'unavailable' => ['label' => 'Verification unavailable', 'title' => 'We could not check Stripe just yet', 'body' => 'Your appointment is still safely reserved. Refresh this page in a moment so we can verify the payment directly with Stripe.', 'tone' => 'bg-sky-50 text-sky-700'],
            default => ['label' => 'Payment pending', 'title' => 'Payment not yet completed', 'body' => 'Stripe has not marked this Checkout Session as paid, so your appointment has not been confirmed.', 'tone' => 'bg-amber-50 text-amber-700'],
        };
    @endphp
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />
        <main class="mx-auto max-w-[640px] px-5 pb-24 md:px-9">
            <div class="rounded-3xl bg-card p-8 text-center shadow-sm md:p-10">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-full {{ $copy['tone'] }}">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
                <p class="eyebrow mt-5">{{ $copy['label'] }}</p>
                <h1 class="display-head mt-2 text-[clamp(1.6rem,3.4vw,2.2rem)] text-ink">{{ $copy['title'] }}</h1>
                <p class="mx-auto mt-3 max-w-[52ch] text-[13px] leading-relaxed text-ink-soft">{{ $copy['body'] }}</p>
                <div class="mt-7 rounded-2xl bg-secondary p-5 text-left text-[12px]">
                    <div class="flex justify-between gap-4"><span class="text-ink-soft">Appointment reference</span><span class="font-medium text-ink">#{{ str_pad($appointment->id, 6, '0', STR_PAD_LEFT) }}</span></div>
                </div>
                <div class="mt-7 flex flex-col gap-2.5 sm:flex-row">
                    @if ($state === 'unavailable')
                        <button type="button" onclick="window.location.reload()" class="flex-1 rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase">Check again</button>
                    @endif
                    <a href="{{ route('appointments.index') }}" class="flex-1 rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.1em] text-ink uppercase">My appointments</a>
                    <a href="{{ route('doctors.index') }}" class="flex-1 rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.1em] text-ink uppercase">Find a doctor</a>
                </div>
            </div>
        </main>
        <x-site-footer />
    </div>
</body>
</html>
