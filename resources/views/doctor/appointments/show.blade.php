@extends('layouts.doctor')

@php
    $title = 'Appointment #'.str_pad($appointment->id, 6, '0', STR_PAD_LEFT);
    $subtitle = $appointment->patient_name;
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Visit details" class="lg:col-span-2">
            <x-slot:action>
                <div class="flex items-center gap-4">
                    <a href="{{ route('doctor.patients.conversations.index', $appointment->user) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">Conversation history</a>
                    <a href="{{ route('doctor.patients.nlp-report.show', $appointment->user) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">NLP classification history</a>
                </div>
            </x-slot:action>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Patient</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_name }}@if ($appointment->patient_age) &middot; {{ $appointment->patient_age }} yrs @endif @if ($appointment->patient_gender) &middot; {{ ucfirst($appointment->patient_gender) }} @endif</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Contact</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->patient_phone }}</dd>
                    <dd class="text-[12px] text-ink-soft">{{ $appointment->patient_email ?? 'No email provided' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Date & time</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->appointment_date->format('D, j M Y') }}, {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Mode</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $appointment->mode === 'online' ? 'Online' : 'In person' }}</dd>
                </div>
            </dl>
            @if ($appointment->reason)
                <div class="mt-4 rounded-2xl bg-secondary p-4">
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Reason for visit</dt>
                    <dd class="mt-1 text-[13px] text-ink">{{ $appointment->reason }}</dd>
                </div>
            @endif

            @if ($appointment->status === 'confirmed')
                <div class="mt-6 flex items-center gap-3 border-t border-border pt-5">
                    <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Mark completed</button>
                    </form>
                    <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Cancel appointment</button>
                    </form>
                </div>
            @endif
        </x-dashboard.panel>

        <x-dashboard.panel title="PHQ-9 / GAD-7 screener">
            @if ($appointment->requires_immediate_escalation)
                <div class="mb-4 rounded-2xl bg-red-100 p-4 text-[12px] font-semibold leading-relaxed text-red-800">Immediate escalation required: the patient reported thoughts of death or self-harm on PHQ-9 item 9. Review and follow the crisis workflow now.</div>
            @endif
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-secondary p-3"><p class="text-[11px] text-ink-soft">PHQ-9</p><p class="font-display text-[18px] text-ink">{{ $appointment->phq9_total ?? '—' }}/27</p><p class="text-[11px] text-ink-soft">{{ str($appointment->phq9_severity ?? 'Not scored')->replace('_', ' ')->title() }}</p></div>
                <div class="rounded-2xl bg-secondary p-3"><p class="text-[11px] text-ink-soft">GAD-7</p><p class="font-display text-[18px] text-ink">{{ $appointment->gad7_total ?? '—' }}/21</p><p class="text-[11px] text-ink-soft">{{ str($appointment->gad7_severity ?? 'Not scored')->title() }}</p></div>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
                <p class="text-[12px] text-ink-soft">Risk level</p>
                @if ($appointment->pre_assessment_risk_level)
                    <x-dashboard.badge :status="$appointment->pre_assessment_risk_level" />
                @else
                    <span class="text-[12px] text-ink-soft">Not assessed</span>
                @endif
            </div>

            @if ($appointment->pre_assessment_summary)
                <p class="mt-4 rounded-2xl bg-secondary p-4 text-[12px] leading-relaxed text-ink">{{ $appointment->pre_assessment_summary }}</p>
            @endif

            @if ($appointment->pre_assessment)
                <ul class="mt-4 space-y-3">
                    @foreach ($appointment->pre_assessment as $answer)
                        <li>
                            <p class="text-[12px] font-medium text-ink">{{ $answer['question'] }}</p>
                            @if (array_key_exists('score', $answer))
                                <p class="mt-0.5 text-[12px] text-ink-soft">{{ $answer['score'] }} · {{ \App\Services\ScreenerAnalyzer::SCALE[$answer['score']] }}</p>
                                @if (! empty($answer['answer']))<p class="mt-0.5 text-[11px] text-ink-soft">Patient said: “{{ $answer['answer'] }}”</p>@endif
                                @if (! empty($answer['extracted_context']))<p class="mt-0.5 text-[11px] text-teal-deep">Context: {{ $answer['extracted_context'] }}</p>@endif
                            @else
                                <p class="mt-0.5 text-[12px] text-ink-soft">{{ $answer['answer'] !== '' ? $answer['answer'] : 'Skipped' }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($appointment->screener_open_notes)
                <p class="mt-4 rounded-2xl bg-secondary p-4 text-[12px] text-ink"><strong>Additional note:</strong> {{ $appointment->screener_open_notes }}</p>
            @endif
        </x-dashboard.panel>

        @if ($appointment->patientNlpReports->isNotEmpty())
            @php($nlpReport = $appointment->patientNlpReports->first()->report)
            <x-dashboard.panel title="Asha conversation report" class="lg:col-span-3">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-[12px] leading-relaxed text-amber-900">AI-generated clinical support summary. This is not a diagnosis and must be checked against the patient conversation and screening responses.</div>

                @if (($nlpReport['risk']['requires_immediate_review'] ?? false) === true)
                    <div class="mt-4 rounded-2xl bg-red-100 p-4 text-[12px] font-semibold leading-relaxed text-red-800">Immediate review required · {{ $nlpReport['risk']['recommended_action'] }}</div>
                @endif

                <p class="mt-5 text-[13px] leading-relaxed text-ink">{{ $nlpReport['summary'] }}</p>

                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach (['presenting_concerns' => 'Presenting concerns', 'symptoms' => 'Reported symptoms', 'stressors' => 'Stressors', 'protective_factors' => 'Protective factors', 'functional_impact' => 'Functional impact'] as $key => $heading)
                        <section class="rounded-2xl bg-secondary p-4">
                            <h3 class="text-[11px] font-semibold tracking-[0.08em] text-ink uppercase">{{ $heading }}</h3>
                            <ul class="mt-3 space-y-3">
                                @forelse ($nlpReport[$key] ?? [] as $item)
                                    <li><p class="text-[12px] font-medium text-ink">{{ $item['label'] }}</p><p class="mt-0.5 text-[11px] leading-relaxed text-ink-soft">{{ $item['evidence'] }} · {{ ucfirst($item['confidence']) }} confidence</p></li>
                                @empty
                                    <li class="text-[11px] text-ink-soft">Not established in this conversation.</li>
                                @endforelse
                            </ul>
                        </section>
                    @endforeach

                    <section class="rounded-2xl bg-secondary p-4">
                        <h3 class="text-[11px] font-semibold tracking-[0.08em] text-ink uppercase">Clinician follow-up</h3>
                        <ul class="mt-3 list-disc space-y-2 pl-4 text-[11px] leading-relaxed text-ink-soft">
                            @forelse ($nlpReport['clinician_follow_up_questions'] ?? [] as $question)<li>{{ $question }}</li>@empty<li>No questions generated.</li>@endforelse
                        </ul>
                    </section>
                </div>
            </x-dashboard.panel>
        @endif
    </div>
@endsection
