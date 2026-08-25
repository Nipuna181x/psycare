@props(['report'])

<div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-[12px] leading-relaxed text-amber-900">AI-generated clinical support summary. This is not a diagnosis and must be checked against the patient conversation and screening responses.</div>

@if (($report['risk']['requires_immediate_review'] ?? false) === true)
    <div class="mt-4 rounded-2xl bg-red-100 p-4 text-[12px] leading-relaxed text-red-800">
        <p class="font-semibold">Immediate review required · {{ $report['risk']['recommended_action'] ?? '' }}</p>
        @if (! empty($report['risk']['evidence']))
            <ul class="mt-2 list-disc space-y-1 pl-4">
                @foreach ($report['risk']['evidence'] as $evidence)
                    <li>{{ $evidence }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif

<p class="mt-5 text-[13px] leading-relaxed text-ink">{{ $report['summary'] ?? 'No summary available.' }}</p>

@if (($report['screening']['available'] ?? false) === true)
    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-secondary p-3">
            <p class="text-[11px] text-ink-soft">PHQ-9</p>
            <p class="font-display text-[16px] text-ink">{{ $report['screening']['phq9_total'] ?? '—' }}/27</p>
            <p class="text-[11px] text-ink-soft">{{ str($report['screening']['phq9_severity'] ?? 'Not scored')->replace('_', ' ')->title() }}</p>
        </div>
        <div class="rounded-2xl bg-secondary p-3">
            <p class="text-[11px] text-ink-soft">GAD-7</p>
            <p class="font-display text-[16px] text-ink">{{ $report['screening']['gad7_total'] ?? '—' }}/21</p>
            <p class="text-[11px] text-ink-soft">{{ str($report['screening']['gad7_severity'] ?? 'Not scored')->title() }}</p>
        </div>
        <div class="rounded-2xl bg-secondary p-3 sm:col-span-2">
            <p class="text-[11px] text-ink-soft">Self-harm flag</p>
            <p class="mt-1 text-[13px] font-medium {{ ($report['screening']['self_harm_flag'] ?? false) ? 'text-red-700' : 'text-ink' }}">{{ ($report['screening']['self_harm_flag'] ?? false) ? 'Yes' : 'No' }}</p>
        </div>
    </div>
@endif

<div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
    @foreach (['presenting_concerns' => 'Presenting concerns', 'symptoms' => 'Reported symptoms', 'stressors' => 'Stressors', 'protective_factors' => 'Protective factors', 'functional_impact' => 'Functional impact'] as $key => $heading)
        <section class="rounded-2xl bg-secondary p-4">
            <h3 class="text-[11px] font-semibold tracking-[0.08em] text-ink uppercase">{{ $heading }}</h3>
            <ul class="mt-3 space-y-3">
                @forelse ($report[$key] ?? [] as $item)
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
            @forelse ($report['clinician_follow_up_questions'] ?? [] as $question)<li>{{ $question }}</li>@empty<li>No questions generated.</li>@endforelse
        </ul>
    </section>

    @if (! empty($report['inconsistencies']))
        <section class="rounded-2xl bg-secondary p-4">
            <h3 class="text-[11px] font-semibold tracking-[0.08em] text-ink uppercase">Inconsistencies</h3>
            <ul class="mt-3 list-disc space-y-2 pl-4 text-[11px] leading-relaxed text-ink-soft">
                @foreach ($report['inconsistencies'] as $inconsistency)<li>{{ $inconsistency }}</li>@endforeach
            </ul>
        </section>
    @endif

    @if (! empty($report['limitations']))
        <section class="rounded-2xl bg-secondary p-4">
            <h3 class="text-[11px] font-semibold tracking-[0.08em] text-ink uppercase">Limitations</h3>
            <ul class="mt-3 list-disc space-y-2 pl-4 text-[11px] leading-relaxed text-ink-soft">
                @foreach ($report['limitations'] as $limitation)<li>{{ $limitation }}</li>@endforeach
            </ul>
        </section>
    @endif
</div>
