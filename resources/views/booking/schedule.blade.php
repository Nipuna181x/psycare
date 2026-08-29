<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose a time — Book {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-booking-header :doctor="$doctor" :step="1" />

        <main class="mx-auto max-w-[840px] px-5 pb-24 md:px-9">
            <div class="mt-8 rounded-3xl bg-card p-6 md:p-8">
                <p class="eyebrow">Step 1 of 4</p>
                <h1 class="display-head mt-2 text-[clamp(1.5rem,3vw,2rem)] text-ink">When would you like to see {{ $doctor->name }}?</h1>

                @if (session('status'))
                    <div class="mt-5 rounded-2xl bg-amber-50 px-4 py-3 text-[13px] text-amber-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-[13px] text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-secondary px-4 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-card text-ink">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2"/><path d="M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2"/><path d="M10 7h4"/><path d="M10 11h4"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[10px] font-medium tracking-[0.08em] text-ink-soft uppercase">Medical center</span>
                            <span class="block truncate text-[13px] font-medium text-ink">{{ $clinic->name }}</span>
                        </span>
                    </div>
                    @if ($canChangeClinic)
                        <a href="{{ route('booking.clinic', $doctor) }}" class="shrink-0 text-[11px] font-semibold text-teal-deep transition-colors hover:text-ink">Change clinic</a>
                    @endif
                </div>

                <form method="POST" action="{{ route('booking.schedule', $doctor) }}" id="schedule-form" class="mt-6">
                    @csrf

                    <label for="appointment_date" class="block text-[12px] font-medium text-ink-soft">Date</label>
                    <input
                        type="date"
                        id="appointment_date"
                        name="appointment_date"
                        required
                        min="{{ now()->toDateString() }}"
                        max="{{ now()->addDays(60)->toDateString() }}"
                        value="{{ old('appointment_date', $saved['appointment_date'] ?? now()->toDateString()) }}"
                        class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[14px] text-ink outline-none"
                    >

                    <p class="mt-6 text-[12px] font-medium text-ink-soft">Available times</p>
                    <div id="time-slots" class="mt-2 grid grid-cols-3 gap-2 sm:grid-cols-4"></div>
                    <input type="hidden" name="appointment_time" id="appointment_time" value="{{ old('appointment_time', $saved['appointment_time'] ?? '') }}" required>
                    <p id="no-slots" hidden class="mt-2 text-[13px] text-ink-soft">No open slots on this date — try another day.</p>

                    <button type="submit" id="continue-button" disabled class="mt-8 w-full rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform enabled:hover:-translate-y-0.5 disabled:opacity-40">Continue</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        (() => {
            const dateInput = document.getElementById('appointment_date');
            const slotsContainer = document.getElementById('time-slots');
            const timeInput = document.getElementById('appointment_time');
            const continueButton = document.getElementById('continue-button');
            const noSlots = document.getElementById('no-slots');
            const slotsUrl = @json(route('booking.slots', $doctor));

            const renderSlots = (slots) => {
                slotsContainer.innerHTML = '';
                noSlots.hidden = slots.length !== 0;
                slots.forEach((slot) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = slot.label;
                    button.dataset.time = slot.time;

                    if (slot.disabled) {
                        button.disabled = true;
                        button.title = 'Already booked';
                        button.className = 'cursor-not-allowed rounded-xl bg-secondary px-3 py-2.5 text-[12px] font-medium text-ink-soft opacity-40 line-through';
                        slotsContainer.append(button);
                        return;
                    }

                    button.className = slot.time === timeInput.value
                        ? 'rounded-xl bg-ink px-3 py-2.5 text-[12px] font-medium text-primary-foreground'
                        : 'rounded-xl bg-secondary px-3 py-2.5 text-[12px] font-medium text-ink transition-colors hover:bg-border';
                    button.addEventListener('click', () => {
                        timeInput.value = slot.time;
                        continueButton.disabled = false;
                        [...slotsContainer.children].forEach((child) => {
                            if (child.disabled) return;
                            child.className = 'rounded-xl bg-secondary px-3 py-2.5 text-[12px] font-medium text-ink transition-colors hover:bg-border';
                        });
                        button.className = 'rounded-xl bg-ink px-3 py-2.5 text-[12px] font-medium text-primary-foreground';
                    });
                    slotsContainer.append(button);
                });
            };

            const loadSlots = async () => {
                continueButton.disabled = true;
                slotsContainer.innerHTML = '<p class="col-span-full text-[12px] text-ink-soft">Loading times…</p>';
                const response = await fetch(`${slotsUrl}?date=${dateInput.value}`, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                renderSlots(data.slots);
                if (data.slots.some((slot) => slot.time === timeInput.value && ! slot.disabled)) {
                    continueButton.disabled = false;
                } else {
                    timeInput.value = '';
                }
            };

            dateInput.addEventListener('change', loadSlots);
            loadSlots();
        })();
    </script>
</body>
</html>
