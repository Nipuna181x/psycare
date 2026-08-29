<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose a clinic — Book {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <header class="mx-auto max-w-[840px] px-5 pt-8 md:px-9">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span>
                    <span class="font-display text-lg font-semibold tracking-[0.08em] uppercase">PsyCare</span>
                </a>
                <a href="{{ route('doctors.show', $doctor) }}" class="text-[12px] text-ink-soft transition-colors hover:text-ink">Exit booking</a>
            </div>
        </header>

        <main class="mx-auto max-w-[840px] px-5 pb-24 md:px-9">
            <div class="mt-8 rounded-3xl bg-card p-6 md:p-8">
                <p class="eyebrow">Before you continue</p>
                <h1 class="display-head mt-2 text-[clamp(1.5rem,3vw,2rem)] text-ink">Which clinic would you like to book at?</h1>
                <p class="mt-2 text-[13px] text-ink-soft">{{ $doctor->name }} sees patients at more than one clinic. Choose the location for this appointment.</p>

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-[13px] text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('booking.clinic', $doctor) }}" class="mt-6 space-y-3">
                    @csrf

                    @foreach ($affiliations as $affiliation)
                        <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-border bg-secondary px-4 py-4 has-[:checked]:border-ink has-[:checked]:bg-ink has-[:checked]:text-primary-foreground">
                            <input type="radio" name="clinic_id" value="{{ $affiliation->clinic_id }}" class="sr-only" {{ (old('clinic_id', $saved['clinic_id'] ?? null)) == $affiliation->clinic_id ? 'checked' : '' }} required>
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-card text-ink">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2"/><path d="M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-[13px] font-medium">{{ $affiliation->clinic->name }}</span>
                                <span class="mt-0.5 block truncate text-[12px] opacity-70">{{ $affiliation->clinic->address }}</span>
                            </span>
                        </label>
                    @endforeach

                    <button type="submit" class="mt-4 w-full rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Continue</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
