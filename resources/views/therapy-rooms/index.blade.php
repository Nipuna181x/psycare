<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Group Therapy — PsyCare</title>
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
            <header>
                <p class="eyebrow">Group Therapy</p>
                <h1 class="display-head mt-2 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">Your sessions</h1>
                <p class="mt-2 text-[13px] text-ink-soft">You're identified to other participants only by your anonymous label — never your name.</p>
            </header>

            <section class="mt-8 space-y-3">
                @forelse ($participations as $participation)
                    @php($room = $participation->therapyRoom)
                    <a href="{{ route('therapy-rooms.show', $room) }}" class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-card p-5">
                        <div class="flex items-center gap-4">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-ink text-[13px] font-semibold text-primary-foreground">{{ mb_strtoupper(mb_substr($room->title, 0, 1)) }}</span>
                            <div>
                                <p class="text-[14px] font-medium text-ink">{{ $room->title }}</p>
                                <p class="mt-0.5 text-[12px] text-ink-soft">You are {{ $participation->anonymous_label }}</p>
                                <p class="mt-0.5 text-[12px] text-ink-soft">{{ $room->scheduled_at->format('D, j M Y · g:i A') }} · {{ $room->duration_minutes }} min</p>
                            </div>
                        </div>
                        <x-dashboard.badge :status="$room->status" />
                    </a>
                @empty
                    <p class="rounded-3xl bg-card p-8 text-[13px] text-ink-soft">You haven't been added to a group session yet.</p>
                @endforelse
            </section>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
