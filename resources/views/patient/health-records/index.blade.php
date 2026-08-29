<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Health Records — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $nlpReport = $latestReport?->report;
        $trendCopy = match ($riskProgression['trend']) {
            'increasing' => ['label' => 'Risk is increasing', 'tone' => 'bg-red-100 text-red-700'],
            'decreasing' => ['label' => 'Risk is decreasing', 'tone' => 'bg-emerald-100 text-emerald-700'],
            'stable' => ['label' => 'Risk is stable', 'tone' => 'bg-secondary text-ink-soft'],
            default => ['label' => 'Not enough data yet', 'tone' => 'bg-secondary text-ink-soft'],
        };
    @endphp
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />

        <main class="mx-auto max-w-[1200px] px-5 pb-24 md:px-9">
            <header>
                <p class="eyebrow">Your record</p>
                <h1 class="display-head mt-2 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">My health records</h1>
                <p class="mt-2 max-w-[65ch] text-[13px] text-ink-soft">Every appointment, prescription, and Lumi AI companion report tied to your account, in one place.</p>
            </header>

            @if (count($riskProgression['points']) > 0)
                <x-dashboard.panel title="Risk progression" subtitle="Based on every Lumi conversation report, oldest to newest" class="mt-8">
                    <span class="inline-flex items-center rounded-full {{ $trendCopy['tone'] }} px-3 py-1.5 text-[11px] font-semibold tracking-[0.06em] uppercase">{{ $trendCopy['label'] }}</span>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @foreach ($riskProgression['points'] as $point)
                            <div class="flex flex-col items-center gap-1.5">
                                <x-dashboard.badge :status="$point['level']" />
                                <span class="text-[10px] text-ink-soft">{{ $point['date'] }}</span>
                            </div>
                            @if (! $loop->last)
                                <svg class="h-3.5 w-3.5 shrink-0 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            @endif
                        @endforeach
                    </div>
                </x-dashboard.panel>
            @endif

            <div class="mt-5 grid gap-5 lg:grid-cols-3">
                <x-dashboard.panel title="Profile" class="lg:col-span-2">
                    <dl class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Name</dt>
                            <dd class="mt-1 text-[13px] font-medium text-ink">{{ $patient->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Contact</dt>
                            <dd class="mt-1 text-[13px] font-medium text-ink">{{ $patient->mobile ?? '—' }}</dd>
                            <dd class="text-[12px] text-ink-soft">{{ $patient->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Total appointments</dt>
                            <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointments->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Most recent visit</dt>
                            <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointments->first()?->appointment_date?->format('j M Y') ?? '—' }}</dd>
                        </div>
                    </dl>
                </x-dashboard.panel>

                <x-dashboard.panel title="Lumi AI companion">
                    @if ($latestReport)
                        <p class="text-[12px] text-ink-soft">Latest report generated {{ $latestReport->generated_at->format('j M Y, g:i A') }}</p>
                        <div class="mt-3">
                            <x-dashboard.badge :status="$nlpReport['risk']['level'] ?? 'unknown'" />
                        </div>
                    @else
                        <p class="text-[13px] text-ink-soft">You haven't used the Lumi AI companion yet.</p>
                        <a href="{{ route('ai-companion.show') }}" class="mt-4 flex items-center justify-center gap-2 rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Talk to Lumi</a>
                    @endif
                </x-dashboard.panel>

                @if (count($chartData['labels']) > 0)
                    <x-dashboard.panel title="Severity trend" subtitle="PHQ-9 and GAD-7 across recorded conversations" class="lg:col-span-2">
                        <div class="h-64">
                            <canvas id="severity-trend-chart" data-chart="{{ json_encode(['labels' => $chartData['labels'], 'phq9' => $chartData['phq9'], 'gad7' => $chartData['gad7']]) }}"></canvas>
                        </div>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Most reported symptoms">
                        @if (count($chartData['symptomCounts']) > 0)
                            <div class="h-64">
                                <canvas id="symptom-frequency-chart" data-chart="{{ json_encode($chartData['symptomCounts']) }}"></canvas>
                            </div>
                        @else
                            <p class="text-[13px] text-ink-soft">No symptoms recorded yet.</p>
                        @endif
                    </x-dashboard.panel>
                @endif

                @if ($reportsByDay->isNotEmpty())
                    <x-dashboard.panel title="Day-by-day Lumi reports" subtitle="Each day's conversation, most recent first" class="lg:col-span-3">
                        <div class="space-y-4">
                            @php($isFirstEntry = true)
                            @foreach ($reportsByDay as $day => $dayReports)
                                @foreach ($dayReports as $dayReport)
                                    @php($dayReportData = $dayReport->report)
                                    <details class="rounded-2xl border border-border" @if ($isFirstEntry) open @php($isFirstEntry = false) @endif>
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4">
                                            <div class="flex items-center gap-3">
                                                <span class="font-display text-[14px] font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($day)->format('l, j M Y') }}</span>
                                                <span class="text-[11px] text-ink-soft">{{ $dayReport->generated_at->format('g:i A') }}</span>
                                            </div>
                                            <x-dashboard.badge :status="$dayReportData['risk']['level'] ?? 'unknown'" />
                                        </summary>

                                        <div class="border-t border-border p-4">
                                            <x-dashboard.lumi-report-body :report="$dayReportData" />
                                        </div>
                                    </details>
                                @endforeach
                            @endforeach
                        </div>
                    </x-dashboard.panel>
                @endif

                <x-dashboard.panel title="Medication history" subtitle="Prescriptions from your appointments" class="lg:col-span-3">
                    @php($appointmentsWithPrescription = $appointments->filter(fn ($appointment) => $appointment->prescription))
                    @if ($appointmentsWithPrescription->isEmpty())
                        <div class="grid min-h-32 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center">
                            <div>
                                <svg class="mx-auto h-6 w-6 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="m10.5 20.5 10-10a4.24 4.24 0 0 0-6-6l-10 10a4.24 4.24 0 0 0 6 6Z"/><path d="m8.5 8.5 7 7"/></svg>
                                <p class="mt-2 text-[12px] font-medium text-ink">No medications recorded yet</p>
                            </div>
                        </div>
                    @else
                        <div class="grid gap-4">
                            @foreach ($appointmentsWithPrescription as $appointment)
                                <section class="overflow-hidden rounded-2xl border border-border">
                                    <header class="flex flex-wrap items-center justify-between gap-2 bg-secondary/60 px-4 py-3">
                                        <p class="text-[12px] font-semibold text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}</p>
                                        <p class="text-[11px] text-ink-soft">{{ $appointment->doctor->name ?? 'Doctor' }} · {{ $appointment->medicalCenter->name ?? 'Clinic' }}</p>
                                    </header>
                                    <ul class="divide-y divide-border">
                                        @foreach ($appointment->prescription->items as $item)
                                            <li class="grid gap-2 px-4 py-3 sm:grid-cols-[1.2fr_0.8fr_0.8fr_0.8fr_1.2fr] sm:items-start">
                                                <div><span class="text-[10px] text-ink-soft sm:hidden">Medicine · </span><span class="text-[12px] font-semibold text-ink">{{ $item->medicine_name }}</span></div>
                                                <p class="text-[11px] text-ink-soft"><span class="sm:hidden">Dosage · </span>{{ $item->dosage }}</p>
                                                <p class="text-[11px] text-ink-soft"><span class="sm:hidden">Frequency · </span>{{ $item->frequency }}</p>
                                                <p class="text-[11px] text-ink-soft"><span class="sm:hidden">Duration · </span>{{ $item->duration ?? '—' }}</p>
                                                <p class="text-[11px] leading-relaxed text-ink-soft">{{ $item->special_instructions ?: '—' }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($appointment->prescription->notes)
                                        <p class="border-t border-border px-4 py-3 text-[11px] leading-relaxed text-ink-soft"><span class="font-semibold text-ink">Notes:</span> {{ $appointment->prescription->notes }}</p>
                                    @endif
                                </section>
                            @endforeach
                        </div>
                    @endif
                </x-dashboard.panel>

                <x-dashboard.panel title="Appointment history" class="lg:col-span-3">
                    @if ($appointments->isEmpty())
                        <p class="text-[13px] text-ink-soft">No appointments recorded yet. <a href="{{ route('doctors.index') }}" class="font-medium text-ink underline">Book a doctor</a>.</p>
                    @else
                        <ul class="divide-y divide-border">
                            @foreach ($appointments as $appointment)
                                <li class="flex flex-wrap items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                    <div>
                                        <p class="text-[13px] font-medium text-ink">{{ $appointment->doctor->name ?? 'Doctor' }} · {{ $appointment->medicalCenter->name ?? 'Clinic' }}</p>
                                        <p class="text-[11px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                                    </div>
                                    <x-dashboard.badge :status="$appointment->status" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-dashboard.panel>
            </div>
        </main>

        <x-site-footer />
    </div>

    @vite('resources/js/patient-charts.js')
</body>
</html>
