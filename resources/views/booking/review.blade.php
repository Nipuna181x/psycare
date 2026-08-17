<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review your booking — {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-background text-ink">
        <x-booking-header :doctor="$doctor" :step="4" />

        <main class="mx-auto max-w-[840px] px-5 pb-24 md:px-9">
            <div class="mt-8 rounded-3xl bg-card p-6 md:p-8">
                <p class="eyebrow">Step 4 of 4</p>
                <h1 class="display-head mt-2 text-[clamp(1.5rem,3vw,2rem)] text-ink">Review & confirm</h1>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-secondary p-5">
                        <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Doctor</p>
                        <p class="mt-1 font-display text-[15px] font-medium text-ink">{{ $doctor->name }}</p>
                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $doctor->medicalCenter->name }}</p>
                    </div>
                    <div class="rounded-2xl bg-secondary p-5">
                        <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">When</p>
                        <p class="mt-1 font-display text-[15px] font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($schedule['appointment_date'])->format('D, j M Y') }}</p>
                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ \Illuminate\Support\Carbon::parse($schedule['appointment_time'])->format('g:i A') }} · {{ $schedule['mode'] === 'online' ? 'Online' : 'In person' }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl bg-secondary p-5">
                    <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Patient</p>
                    <dl class="mt-2 grid gap-x-6 gap-y-1.5 text-[13px] text-ink sm:grid-cols-2">
                        <div class="flex justify-between sm:block"><dt class="text-ink-soft sm:hidden">Name</dt><dd>{{ $details['patient_name'] }}</dd></div>
                        <div class="flex justify-between sm:block"><dt class="text-ink-soft sm:hidden">Phone</dt><dd>{{ $details['patient_phone'] }}</dd></div>
                        @if (! empty($details['patient_age']))
                            <div class="flex justify-between sm:block"><dt class="text-ink-soft sm:hidden">Age</dt><dd>{{ $details['patient_age'] }}</dd></div>
                        @endif
                        @if (! empty($details['patient_email']))
                            <div class="flex justify-between sm:block"><dt class="text-ink-soft sm:hidden">Email</dt><dd>{{ $details['patient_email'] }}</dd></div>
                        @endif
                    </dl>
                    @if (! empty($details['reason']))
                        <p class="mt-3 text-[13px] text-ink-soft">"{{ $details['reason'] }}"</p>
                    @endif
                </div>

                <div class="mt-4 rounded-2xl border border-border p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">AI pre-assessment summary</p>
                        <x-dashboard.badge :status="$analysis['risk_level']" />
                    </div>
                    <p class="mt-2 text-[13px] leading-relaxed text-ink-soft">{{ $analysis['summary'] }}</p>

                    <details class="mt-3">
                        <summary class="cursor-pointer text-[12px] font-medium text-ink">View full answers</summary>
                        <ul class="mt-3 space-y-2.5">
                            @foreach ($assessment['answers'] as $answer)
                                <li>
                                    <p class="text-[12px] font-medium text-ink">{{ $answer['question'] }}</p>
                                    <p class="mt-0.5 text-[12px] text-ink-soft">{{ $answer['answer'] !== '' ? $answer['answer'] : 'Skipped' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </details>
                </div>

                <div class="mt-4 flex items-center justify-between rounded-2xl bg-ink px-5 py-4 text-primary-foreground">
                    <p class="text-[13px]">Consultation fee</p>
                    <p class="font-display text-[16px] font-medium">{{ $doctor->consultation_fee ? 'LKR '.number_format($doctor->consultation_fee) : 'On request' }}</p>
                </div>

                <form method="POST" action="{{ route('booking.confirm', $doctor) }}" class="mt-6 flex items-center gap-3">
                    @csrf
                    <a href="{{ route('booking.assessment', $doctor) }}" class="rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Back</a>
                    <button type="submit" class="flex-1 rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Confirm booking</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
