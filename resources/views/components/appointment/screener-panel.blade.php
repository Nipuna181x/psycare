@props(['appointment'])

@php
    $riskLevel = $appointment->pre_assessment_risk_level;
@endphp

<section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="screener-heading">
    <div class="flex flex-col gap-3 border-b border-border pb-5 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-[10px] font-semibold tracking-[0.12em] text-sky-700 uppercase">Pre-assessment</p>
            <h2 id="screener-heading" class="mt-1 font-display text-[17px] font-medium text-ink">PHQ-9 / GAD-7 screener</h2>
            <p class="mt-1 text-[12px] text-ink-soft">Review severity, risk signals, and the patient's own words before the visit.</p>
        </div>
        @if ($riskLevel)
            <x-dashboard.badge :status="$riskLevel" />
        @else
            <span class="inline-flex rounded-full bg-secondary px-2.5 py-1 text-[10px] font-semibold tracking-[0.06em] text-ink-soft uppercase">Not assessed</span>
        @endif
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <article class="rounded-2xl border border-border bg-white p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">PHQ-9 · Depression</p>
                    <p class="mt-2 font-display text-[28px] font-medium leading-none text-ink">{{ $appointment->phq9_total ?? '—' }}<span class="text-[13px] text-ink-soft">/27</span></p>
                </div>
                <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-medium text-ink">{{ str($appointment->phq9_severity ?? 'Not scored')->replace('_', ' ')->title() }}</span>
            </div>
            <progress aria-label="PHQ-9 severity score" value="{{ $appointment->phq9_total ?? 0 }}" max="27" class="mt-5 h-1.5 w-full overflow-hidden rounded-full accent-sky-700"></progress>
            <div class="mt-1.5 flex justify-between text-[9px] text-ink-soft"><span>Minimal</span><span>Severe</span></div>
        </article>

        <article class="rounded-2xl border border-border bg-white p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-semibold tracking-[0.1em] text-ink-soft uppercase">GAD-7 · Anxiety</p>
                    <p class="mt-2 font-display text-[28px] font-medium leading-none text-ink">{{ $appointment->gad7_total ?? '—' }}<span class="text-[13px] text-ink-soft">/21</span></p>
                </div>
                <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-medium text-ink">{{ str($appointment->gad7_severity ?? 'Not scored')->replace('_', ' ')->title() }}</span>
            </div>
            <progress aria-label="GAD-7 severity score" value="{{ $appointment->gad7_total ?? 0 }}" max="21" class="mt-5 h-1.5 w-full overflow-hidden rounded-full accent-sky-700"></progress>
            <div class="mt-1.5 flex justify-between text-[9px] text-ink-soft"><span>Minimal</span><span>Severe</span></div>
        </article>
    </div>

    @if ($appointment->pre_assessment_summary)
        <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 p-4">
            <div class="flex items-start gap-3">
                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-white text-sky-700 shadow-[0_1px_0_0_rgba(14,116,144,0.14)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.5 4a2.5 2.5 0 0 1 5 0v.5a2.5 2.5 0 0 1 0 5h-5a2.5 2.5 0 0 1 0-5Z"/><path d="M6 9.5a2.5 2.5 0 0 0 0 5h.5a2.5 2.5 0 0 0 5 0v-5"/><path d="M18 9.5a2.5 2.5 0 0 1 0 5h-.5a2.5 2.5 0 0 1-5 0v-5"/><path d="M9.5 14.5v.5a2.5 2.5 0 0 0 5 0v-.5"/><path d="M12 2v20"/></svg>
                </span>
                <div>
                    <p class="text-[10px] font-semibold tracking-[0.1em] text-sky-700 uppercase">AI-generated clinical summary</p>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-slate-700">{{ $appointment->pre_assessment_summary }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-6 border-t border-border pt-5">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h3 class="font-display text-[15px] font-medium text-ink">Question-by-question review</h3>
                <p class="mt-1 text-[11px] text-ink-soft">Expand each response for the patient's quote and extracted context.</p>
            </div>
            @if ($appointment->pre_assessment)
                <span class="shrink-0 text-[10px] font-medium text-ink-soft">{{ count($appointment->pre_assessment) }} responses</span>
            @endif
        </div>

        @if ($appointment->pre_assessment)
            <div class="mt-4 grid gap-3">
                @foreach ($appointment->pre_assessment as $answer)
                    <details class="group rounded-2xl border border-border bg-white open:border-sky-200 open:shadow-[0_10px_28px_-24px_rgba(14,116,144,0.45)]">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 p-4 marker:content-none">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-lg bg-secondary text-[10px] font-semibold text-ink-soft">{{ $loop->iteration }}</span>
                                <p class="text-[12px] font-semibold leading-relaxed text-ink">{{ $answer['question'] }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if (array_key_exists('score', $answer))
                                    <span class="rounded-full bg-secondary px-2.5 py-1 text-[10px] font-semibold text-ink">Score {{ $answer['score'] }}</span>
                                @endif
                                <svg class="h-4 w-4 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </summary>
                        <div class="border-t border-border px-4 pt-4 pb-5 sm:pl-13">
                            @if (array_key_exists('score', $answer))
                                <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">{{ \App\Services\ScreenerAnalyzer::SCALE[$answer['score']] }}</p>
                                @if (! empty($answer['answer']))
                                    <blockquote class="mt-3 border-l-2 border-sky-300 pl-3 text-[13px] leading-relaxed text-slate-700 italic">“{{ $answer['answer'] }}”</blockquote>
                                @else
                                    <p class="mt-3 text-[12px] text-ink-soft">No spoken response was recorded.</p>
                                @endif
                                @if (! empty($answer['extracted_context']))
                                    <p class="mt-3 rounded-xl bg-secondary px-3 py-2.5 text-[11px] leading-relaxed text-ink-soft"><span class="font-semibold text-ink">Context / translation:</span> {{ $answer['extracted_context'] }}</p>
                                @endif
                            @else
                                <p class="text-[12px] leading-relaxed text-ink-soft">{{ $answer['answer'] !== '' ? $answer['answer'] : 'Skipped' }}</p>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        @else
            <div class="mt-4 grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-6 py-8 text-center">
                <div>
                    <svg class="mx-auto h-5 w-5 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <p class="mt-2 text-[12px] font-medium text-ink">Pre-assessment pending</p>
                    <p class="mt-1 text-[11px] text-ink-soft">Responses will appear here when the patient completes the screener.</p>
                </div>
            </div>
        @endif
    </div>

    @if ($appointment->screener_open_notes)
        <div class="mt-5 border-t border-border pt-5">
            <div class="rounded-2xl bg-secondary p-4">
                <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Patient's additional note</p>
                <p class="mt-2 text-[12px] leading-relaxed text-ink">{{ $appointment->screener_open_notes }}</p>
            </div>
        </div>
    @endif
</section>
