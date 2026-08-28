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

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl bg-red-50 px-4 py-3 text-[13px] text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

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

                    <p class="mt-6 text-[12px] font-medium text-ink-soft">Consultation mode</p>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach (['in_person' => 'In person', 'online' => 'Online'] as $value => $label)
                            <label class="flex cursor-pointer items-center justify-center rounded-2xl border border-border bg-secondary px-4 py-3 text-[13px] font-medium text-ink has-[:checked]:border-ink has-[:checked]:bg-ink has-[:checked]:text-primary-foreground">
                                <input type="radio" name="mode" value="{{ $value }}" class="sr-only" {{ old('mode', $saved['mode'] ?? 'in_person') === $value ? 'checked' : '' }}>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>

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
