@extends('layouts.doctor')

@php
    $title = $patient->name;
    $subtitle = 'Patient profile';
    $nlpReport = $latestReport?->report;
    $trendCopy = match ($riskProgression['trend']) {
        'increasing' => ['label' => 'Risk is increasing', 'tone' => 'bg-red-100 text-red-700'],
        'decreasing' => ['label' => 'Risk is decreasing', 'tone' => 'bg-emerald-100 text-emerald-700'],
        'stable' => ['label' => 'Risk is stable', 'tone' => 'bg-secondary text-ink-soft'],
        default => ['label' => 'Not enough data yet', 'tone' => 'bg-secondary text-ink-soft'],
    };
@endphp

@section('content')
    @if (session('status'))
        <div class="mb-5 rounded-2xl bg-sky-50 px-4 py-3 text-[13px] text-sky-700">{{ session('status') }}</div>
    @endif

    @if (count($riskProgression['points']) > 0)
        <x-dashboard.panel title="Risk progression" subtitle="Based on every Lumi conversation report, oldest to newest" class="mb-5">
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

    <div class="grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Profile" class="lg:col-span-2">
            <x-slot:action>
                <a href="{{ route('doctor.patients.nlp-report.show', $patient) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">Classification history</a>
            </x-slot:action>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Name</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $patient->name }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Contact</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $patient->mobile }}</dd>
                    <dd class="text-[12px] text-ink-soft">{{ $patient->email }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Appointments with you</dt>
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
                <a href="{{ route('doctor.patients.reports.history-download', $patient) }}" class="mt-4 flex items-center justify-center gap-2 rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>
                    Download Full Report
                </a>
            @else
                <p class="text-[13px] text-ink-soft">This patient has not used the Lumi AI companion yet.</p>
            @endif

            @if ($pendingSessions > 0)
                <form method="POST" action="{{ route('doctor.patients.reports.generate', $patient) }}" class="mt-3">
                    @csrf
                    <p class="mb-2 text-[11px] text-amber-700">{{ $pendingSessions }} conversation(s) ended without a report being generated.</p>
                    <button type="submit" class="w-full rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">
                        Generate missing report(s)
                    </button>
                </form>
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
                                    <div class="flex items-center gap-3">
                                        <x-dashboard.badge :status="$dayReportData['risk']['level'] ?? 'unknown'" />
                                        <a href="{{ route('doctor.patients.reports.download', [$patient, $dayReport]) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline" onclick="event.stopPropagation()">Download PDF</a>
                                    </div>
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

        <x-dashboard.panel title="Medication History" subtitle="Prescriptions recorded during appointments with you" class="lg:col-span-3">
            @php($appointmentsWithMedications = $appointments->filter(fn ($appointment) => $appointment->prescriptions->isNotEmpty()))
            @if ($appointmentsWithMedications->isEmpty())
                <div class="grid min-h-32 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center">
                    <div>
                        <svg class="mx-auto h-6 w-6 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="m10.5 20.5 10-10a4.24 4.24 0 0 0-6-6l-10 10a4.24 4.24 0 0 0 6 6Z"/><path d="m8.5 8.5 7 7"/></svg>
                        <p class="mt-2 text-[12px] font-medium text-ink">No medications recorded yet</p>
                    </div>
                </div>
            @else
                <div class="grid gap-4">
                    @foreach ($appointmentsWithMedications as $appointment)
                        <section class="overflow-hidden rounded-2xl border border-border">
                            <header class="flex flex-wrap items-center justify-between gap-2 bg-secondary/60 px-4 py-3">
                                <p class="text-[12px] font-semibold text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}</p>
                                <p class="text-[11px] text-ink-soft">{{ $appointment->prescriptions->first()->doctor->name ?? 'Doctor' }}</p>
                            </header>
                            <ul class="divide-y divide-border">
                                @foreach ($appointment->prescriptions as $prescription)
                                    <li class="grid gap-2 px-4 py-3 sm:grid-cols-[1.2fr_0.8fr_0.8fr_1.5fr] sm:items-start">
                                        <div><span class="text-[10px] text-ink-soft sm:hidden">Medication · </span><span class="text-[12px] font-semibold text-ink">{{ $prescription->medication_name }}</span></div>
                                        <p class="text-[11px] text-ink-soft"><span class="sm:hidden">Dosage · </span>{{ $prescription->dosage }}</p>
                                        <p class="text-[11px] text-ink-soft"><span class="sm:hidden">Frequency · </span>{{ $prescription->frequency }}</p>
                                        <p class="text-[11px] leading-relaxed text-ink-soft">{{ $prescription->notes ?: 'No duration or notes recorded.' }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel title="Appointment history" class="lg:col-span-3">
            @if ($appointments->isEmpty())
                <p class="text-[13px] text-ink-soft">No appointments recorded yet.</p>
            @else
                <ul class="divide-y divide-border">
                    @foreach ($appointments as $appointment)
                        <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div>
                                <p class="text-[13px] font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}</p>
                                <p class="text-[11px] text-ink-soft">{{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }} · {{ $appointment->mode === 'online' ? 'Online' : 'In person' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-dashboard.badge :status="$appointment->status" />
                                <a href="{{ route('doctor.appointments.show', $appointment) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">View</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-dashboard.panel>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/patient-charts.js')
@endpush
