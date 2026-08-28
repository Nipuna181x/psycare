<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Care Access — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />

        <main class="mx-auto max-w-[1000px] px-5 pb-24 md:px-9">
            <header>
                <p class="eyebrow">Settings</p>
                <h1 class="display-head mt-2 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">Care access</h1>
            </header>
            <p class="mt-2 max-w-[65ch] text-[13px] text-ink-soft">Control which of your doctors can see your appointment and prescription history from other clinics. Doctors can always see the visits you've had with them, regardless of this setting.</p>

            @if (session('status'))
                <div class="mt-4 rounded-2xl bg-sky-50 px-4 py-3 text-[13px] text-sky-700">{{ session('status') }}</div>
            @endif

            <section class="mt-8 rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
                @forelse ($doctors as $doctor)
                    @php($consent = $doctor->consentsReceived->first())
                    @php($granted = $consent && $consent->isActive())
                    <div class="flex items-center justify-between gap-4 border-b border-border py-4 first:pt-0 last:border-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">Dr. {{ $doctor->name }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $doctor->specialization ?? 'General practice' }}</p>
                        </div>
                        <form method="POST" action="{{ route('settings.care-access.update', $doctor) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="grant" value="{{ $granted ? '0' : '1' }}">
                            <button type="submit" class="shrink-0 rounded-xl {{ $granted ? 'border border-red-200 bg-white text-red-700 hover:bg-red-50' : 'bg-sky-700 text-white hover:bg-sky-800' }} px-4 py-2.5 text-[11px] font-semibold tracking-[0.08em] uppercase transition-colors">
                                {{ $granted ? 'Revoke access' : 'Grant access' }}
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-[13px] text-ink-soft">You have no doctors on file yet.</p>
                @endforelse
            </section>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
