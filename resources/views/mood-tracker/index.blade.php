<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mood Tracker — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/patient-charts.js'])
</head>
<body>
    @php
        $moods = [
            1 => ['emoji' => '😞', 'label' => 'Very low', 'tone' => 'peer-checked:border-red-400 peer-checked:bg-red-50 peer-checked:ring-red-100'],
            2 => ['emoji' => '🙁', 'label' => 'Low', 'tone' => 'peer-checked:border-orange-400 peer-checked:bg-orange-50 peer-checked:ring-orange-100'],
            3 => ['emoji' => '😐', 'label' => 'Neutral', 'tone' => 'peer-checked:border-amber-400 peer-checked:bg-amber-50 peer-checked:ring-amber-100'],
            4 => ['emoji' => '🙂', 'label' => 'Good', 'tone' => 'peer-checked:border-lime-500 peer-checked:bg-lime-50 peer-checked:ring-lime-100'],
            5 => ['emoji' => '😊', 'label' => 'Great', 'tone' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:ring-emerald-100'],
        ];
        $tags = ['anxious', 'calm', 'stressed', 'sad', 'happy', 'angry', 'tired', 'energetic', 'hopeful', 'overwhelmed'];
        $selectedTags = old('mood_tags', $todayEntry?->mood_tags ?? []);
        $selectedMood = (int) old('mood_score', $todayEntry?->mood_score);
    @endphp

    <div class="min-h-screen bg-[linear-gradient(180deg,#fffaf2_0%,var(--background)_38%)] text-ink">
        <x-patient-nav active="mood-tracker" />

        <main class="mx-auto max-w-[1100px] px-5 pb-24 md:px-9">
            <header class="max-w-[48rem]">
                <p class="eyebrow text-amber-700">Daily wellbeing</p>
                <h1 class="display-head mt-2 text-[clamp(2rem,4vw,3rem)] text-ink">A small check-in for today</h1>
                <p class="mt-3 max-w-[58ch] text-[14px] leading-relaxed text-ink-soft">Take a quiet moment to notice how you feel. There are no right or wrong answers.</p>
            </header>

            @if (session('status'))
                <div class="mt-6 flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800">
                    <span aria-hidden="true">✓</span>{{ session('status') }}
                </div>
            @endif

            <section class="mt-8 rounded-[2rem] bg-card p-5 shadow-[0_18px_50px_-36px_rgba(120,83,38,0.35)] md:p-8" aria-labelledby="check-in-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><p class="text-[10px] font-semibold tracking-[0.12em] text-amber-700 uppercase">Today's check-in</p><h2 id="check-in-heading" class="mt-1 font-display text-[21px] font-medium text-ink">How are you feeling today?</h2></div>
                    @if ($todayEntry)<span class="rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-semibold tracking-[0.08em] text-emerald-700 uppercase">Checked in</span>@endif
                </div>

                <form method="POST" action="{{ route('mood-tracker.store') }}" class="mt-7 grid gap-7">
                    @csrf
                    <fieldset>
                        <legend class="sr-only">Choose today's mood</legend>
                        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-5">
                            @foreach ($moods as $score => $mood)
                                <label class="cursor-pointer">
                                    <input type="radio" name="mood_score" value="{{ $score }}" required @checked($selectedMood === $score) class="peer sr-only">
                                    <span class="flex min-h-28 flex-col items-center justify-center gap-2 rounded-2xl border border-border bg-white p-3 text-center transition peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-amber-500 peer-checked:ring-4 {{ $mood['tone'] }} hover:-translate-y-0.5">
                                        <span class="text-4xl" aria-hidden="true">{{ $mood['emoji'] }}</span>
                                        <span class="text-[11px] font-semibold text-ink">{{ $mood['label'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('mood_score')<p class="mt-2 text-[11px] text-red-700">{{ $message }}</p>@enderror
                    </fieldset>

                    <fieldset>
                        <legend class="text-[12px] font-semibold text-ink">What else are you noticing? <span class="font-normal text-ink-soft">Optional</span></legend>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="mood_tags[]" value="{{ $tag }}" @checked(in_array($tag, $selectedTags, true)) class="peer sr-only">
                                    <span class="inline-flex min-h-10 items-center rounded-full border border-border bg-white px-4 py-2 text-[11px] font-medium text-ink-soft transition hover:border-amber-300 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-amber-500 peer-checked:border-amber-300 peer-checked:bg-amber-50 peer-checked:text-amber-900">{{ ucfirst($tag) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('mood_tags.*')<p class="mt-2 text-[11px] text-red-700">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="grid gap-5 md:grid-cols-[0.55fr_1.45fr]">
                        <label class="text-[12px] font-semibold text-ink">Sleep hours <span class="font-normal text-ink-soft">Optional</span>
                            <input type="number" name="sleep_hours" value="{{ old('sleep_hours', $todayEntry?->sleep_hours) }}" min="0" max="12" step="0.5" placeholder="e.g. 7.5" class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100">
                            @error('sleep_hours')<span class="mt-1 block text-[11px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="text-[12px] font-semibold text-ink">Anything you want to add? <span class="font-normal text-ink-soft">Optional</span>
                            <textarea name="note" rows="3" maxlength="1000" placeholder="A short note about your day…" class="mt-2 w-full resize-y rounded-2xl border border-border bg-white px-4 py-3 text-[13px] leading-relaxed text-ink outline-none placeholder:text-muted-foreground focus:border-amber-400 focus:ring-2 focus:ring-amber-100">{{ old('note', $todayEntry?->note) }}</textarea>
                            @error('note')<span class="mt-1 block text-[11px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                    </div>

                    <button type="submit" class="justify-self-start rounded-full bg-ink px-7 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">
                        {{ $todayEntry ? "Update today's entry" : "Save today's entry" }}
                    </button>
                </form>
            </section>

            <section class="mt-8 grid gap-5">
                <article class="rounded-[2rem] bg-card p-5 shadow-[0_18px_50px_-36px_rgba(15,23,42,0.3)] md:p-7">
                    <h2 class="font-display text-[18px] font-medium text-ink">Your last 30 days</h2>
                    <p class="mt-1 text-[11px] text-ink-soft">A gentle view of how your mood has moved over time.</p>
                    <x-mood-trend-chart :chart-data="$moodChartData" class="mt-5" />
                </article>

                <article class="rounded-[2rem] bg-card p-5 shadow-[0_18px_50px_-36px_rgba(15,23,42,0.3)] md:p-7">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <h2 class="font-display text-[18px] font-medium text-ink">Mood history</h2>
                            <p class="mt-1 text-[11px] text-ink-soft">Your daily check-ins, newest first.</p>
                        </div>
                        @if ($entries->isNotEmpty())
                            <span class="rounded-full bg-amber-50 px-3 py-1.5 text-[10px] font-semibold text-amber-800">{{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }}</span>
                        @endif
                    </div>
                    @if ($entries->isEmpty())
                        <div class="mt-5 rounded-2xl border border-dashed border-amber-200 bg-amber-50/60 p-6 text-center"><span class="text-3xl" aria-hidden="true">🌱</span><p class="mt-3 text-[12px] font-medium text-ink">Your first check-in can start right here.</p><p class="mt-1 text-[11px] text-ink-soft">Choose how you feel above whenever you're ready.</p></div>
                    @else
                        <div class="mt-5 grid gap-3 md:grid-cols-2">
                            @foreach ($entries as $entry)
                                @php($mood = $moods[$entry->mood_score])
                                <article class="rounded-2xl border border-border bg-white p-4">
                                    <div class="flex items-start gap-3"><span class="text-2xl" aria-hidden="true">{{ $mood['emoji'] }}</span><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center justify-between gap-2"><p class="text-[12px] font-semibold text-ink">{{ $entry->entry_date->isToday() ? 'Today' : $entry->entry_date->format('D, j M Y') }}</p><span class="text-[10px] font-medium text-ink-soft">{{ $mood['label'] }} · {{ $entry->mood_score }}/5</span></div>
                                        @if ($entry->mood_tags)<div class="mt-2 flex flex-wrap gap-1.5">@foreach ($entry->mood_tags as $tag)<span class="rounded-full bg-amber-50 px-2 py-1 text-[9px] font-medium text-amber-800">{{ ucfirst($tag) }}</span>@endforeach</div>@endif
                                        @if ($entry->sleep_hours !== null)<p class="mt-2 text-[10px] text-ink-soft">Sleep: {{ rtrim(rtrim($entry->sleep_hours, '0'), '.') }} hours</p>@endif
                                        @if ($entry->note)<p class="mt-2 line-clamp-2 text-[11px] leading-relaxed text-ink-soft">{{ $entry->note }}</p>@endif
                                    </div></div>
                                </article>
                            @endforeach
                        </div>
                        @if ($entries->hasPages())<div class="mt-5">{{ $entries->links() }}</div>@endif
                    @endif
                </article>
            </section>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
