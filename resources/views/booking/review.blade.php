<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review your booking — {{ $doctor->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @include('partials.favicon')

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
                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $clinic?->name }}</p>
                    </div>
                    <div class="rounded-2xl bg-secondary p-5">
                        <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">When</p>
                        <p class="mt-1 font-display text-[15px] font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($schedule['appointment_date'])->format('D, j M Y') }}</p>
                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ \Illuminate\Support\Carbon::parse($schedule['appointment_time'])->format('g:i A') }}</p>
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
                    @if ($analysis === null)
                        <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Voice screening</p>
                        <p class="mt-3 rounded-xl bg-secondary p-3 text-[13px] text-ink">You chose to skip the screening. You can still book this appointment — your doctor will follow up with you directly.</p>
                    @else
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Validated screener results</p>
                            @if ($analysis['requires_immediate_escalation'])
                                <span class="rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-red-700">Immediate support needed</span>
                            @endif
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <p class="rounded-xl bg-secondary p-3 text-[13px] text-ink"><strong>PHQ-9:</strong> {{ $analysis['phq9']['total'] }}/27 · {{ str($analysis['phq9']['severity'])->replace('_', ' ')->title() }}</p>
                            <p class="rounded-xl bg-secondary p-3 text-[13px] text-ink"><strong>GAD-7:</strong> {{ $analysis['gad7']['total'] }}/21 · {{ str($analysis['gad7']['severity'])->title() }}</p>
                        </div>

                        <details class="mt-3">
                            <summary class="cursor-pointer text-[12px] font-medium text-ink">View full answers</summary>
                            <ul class="mt-3 space-y-2.5">
                                @foreach ($assessment['answers'] as $answer)
                                    <li>
                                        <p class="text-[12px] font-medium text-ink">{{ $answer['question'] }}</p>
                                        <p class="mt-0.5 text-[12px] text-ink-soft">{{ $answer['score'] }} · {{ \App\Services\ScreenerAnalyzer::SCALE[$answer['score']] }}</p>
                                        @if ($answer['answer'] !== '')<p class="mt-0.5 text-[11px] text-ink-soft">Patient said: “{{ $answer['answer'] }}”</p>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                    @if (! empty($assessment['open_notes']))
                        <p class="mt-3 rounded-xl bg-secondary p-3 text-[12px] text-ink"><strong>Additional note:</strong> {{ $assessment['open_notes'] }}</p>
                    @endif
                </div>

                <div class="mt-4 rounded-2xl bg-ink px-5 py-4 text-primary-foreground">
                    <div class="flex items-center justify-between">
                        <p class="text-[13px] text-primary-foreground/80">Doctor's session fee</p>
                        <p class="text-[13px] font-medium">LKR {{ number_format($doctorFee) }}</p>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-[13px] text-primary-foreground/80">{{ $clinic->name }} facility fee</p>
                        <p class="text-[13px] font-medium">LKR {{ number_format($clinicFee) }}</p>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-white/15 pt-3">
                        <p class="text-[13px]">Total</p>
                        <p class="font-display text-[16px] font-medium">LKR {{ number_format($totalFee) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('booking.confirm', $doctor) }}" class="mt-6 flex items-center gap-3">
                    @csrf
                    <a href="{{ route('booking.assessment', $doctor) }}" class="rounded-2xl bg-secondary px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Back</a>
                    <button type="submit" class="flex-1 rounded-2xl bg-ink px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Confirm &amp; Pay</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
