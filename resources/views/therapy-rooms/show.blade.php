<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $therapyRoom->title }} — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />

        <main class="mx-auto max-w-[720px] px-5 pb-24 md:px-9">
            <header>
                <p class="eyebrow">Group Therapy</p>
                <h1 class="display-head mt-2 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">{{ $therapyRoom->title }}</h1>
            </header>

            <div class="mt-8 rounded-3xl bg-card p-6">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-[13px] text-ink-soft">You'll appear to other participants as</p>
                    <x-dashboard.badge :status="$therapyRoom->status" />
                </div>
                <p class="mt-1 font-display text-[20px] text-ink">{{ $participant->anonymous_label }}</p>

                @if ($therapyRoom->topic)
                    <p class="mt-4 rounded-2xl bg-secondary p-4 text-[13px] leading-relaxed text-ink">{{ $therapyRoom->topic }}</p>
                @endif

                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Date & time</dt>
                        <dd class="mt-1 text-[13px] font-medium text-ink">{{ $therapyRoom->scheduled_at->format('D, j M Y · g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Duration</dt>
                        <dd class="mt-1 text-[13px] font-medium text-ink">{{ $therapyRoom->duration_minutes }} minutes</dd>
                    </div>
                </dl>

                <div class="mt-6 border-t border-border pt-5">
                    @if ($therapyRoom->status === 'live')
                        <a href="{{ route('therapy-rooms.session', $therapyRoom) }}" class="inline-flex rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Join session</a>
                    @elseif ($therapyRoom->status === 'scheduled')
                        <p class="text-[12px] text-ink-soft">This session hasn't started yet. Come back at the scheduled time.</p>
                    @elseif ($therapyRoom->status === 'completed')
                        <p class="text-[12px] text-ink-soft">This session has ended.</p>
                    @else
                        <p class="text-[12px] text-ink-soft">This session was cancelled.</p>
                    @endif
                </div>
            </div>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
