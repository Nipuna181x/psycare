@php
    $guardPrefix = auth('doctor')->check() ? 'doctor' : 'admin';

    /**
     * Recomputed directly from the raw history on every render, independent of anything
     * the controller passes in, so this can never be silently skipped by a reporting change.
     */
    $selfHarmHistory = $results->filter(fn ($result) => (bool) $result->self_harm_flag);

    $trendCopy = match ($trend) {
        'improving' => ['label' => 'Improving', 'tone' => 'bg-emerald-100 text-emerald-700'],
        'worsening' => ['label' => 'Worsening', 'tone' => 'bg-red-100 text-red-700'],
        'stable' => ['label' => 'Stable', 'tone' => 'bg-secondary text-ink-soft'],
        default => ['label' => 'Insufficient history', 'tone' => 'bg-secondary text-ink-soft'],
    };
@endphp

<div class="mb-5 flex items-start justify-between gap-4 print:hidden">
    <div>
        <h1 class="font-display text-[20px] font-medium text-ink">NLP classification report</h1>
        <p class="mt-1 text-[13px] text-ink-soft">{{ $patient->name }} &middot; {{ $results->count() }} recorded {{ Str::plural('entry', $results->count()) }}</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route("{$guardPrefix}.patients.conversations.index", $patient) }}" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">
            Conversation history
        </a>
        <form method="POST" action="{{ route(auth('doctor')->check() ? 'doctor.patients.nlp-report.sync' : 'admin.patients.nlp-report.sync', $patient) }}">
            @csrf
            <button type="submit" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">
                Sync now
            </button>
        </form>
        <button type="button" onclick="window.print()" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">
            Print report
        </button>
    </div>
</div>

@if ($selfHarmHistory->isNotEmpty())
    <div class="mb-5 rounded-2xl border-2 border-red-400 bg-red-100 p-5 text-red-900">
        <p class="text-[12px] font-bold tracking-[0.08em] uppercase">⚠ Self-harm signal detected in patient history</p>
        <p class="mt-2 text-[13px] leading-relaxed">
            {{ $selfHarmHistory->count() }} of {{ $results->count() }} entries in this patient's NLP classification history
            were flagged for self-harm content (most recent: {{ $selfHarmHistory->last()->entry_date->format('j M Y') }}).
            <strong>This is an automated screening signal only, not a standalone safety determination.</strong>
            It must be clinically reviewed against the full patient record before any decision is made.
        </p>
    </div>
@endif

<div class="grid gap-5 lg:grid-cols-3">
    <x-dashboard.panel title="Current status" class="lg:col-span-2">
        @if ($latest)
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">Risk level</p>
                    <div class="mt-1"><x-dashboard.badge :status="$latest->risk_level" /></div>
                </div>
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">PHQ-9</p>
                    <p class="mt-1 text-[13px] font-medium text-ink">{{ str($latest->phq9_severity ?? 'Not scored')->replace('_', ' ')->title() }}</p>
                </div>
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">GAD-7</p>
                    <p class="mt-1 text-[13px] font-medium text-ink">{{ str($latest->gad7_severity ?? 'Not scored')->replace('_', ' ')->title() }}</p>
                </div>
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">As of</p>
                    <p class="mt-1 text-[13px] font-medium text-ink">{{ $latest->entry_date->format('j M Y') }}</p>
                </div>
            </div>
        @else
            <p class="text-[13px] text-ink-soft">No NLP classification results have been recorded for this patient yet.</p>
        @endif
    </x-dashboard.panel>

    <x-dashboard.panel title="Trend">
        <span class="inline-flex items-center rounded-full {{ $trendCopy['tone'] }} px-3 py-1.5 text-[11px] font-semibold tracking-[0.06em] uppercase">{{ $trendCopy['label'] }}</span>
        <p class="mt-3 text-[12px] leading-relaxed text-ink-soft">
            @if ($trend)
                Based on comparing the earliest ({{ $results->first()->entry_date->format('j M Y') }}, {{ ucfirst($results->first()->risk_level) }})
                and latest ({{ $results->last()->entry_date->format('j M Y') }}, {{ ucfirst($results->last()->risk_level) }}) recorded risk levels.
            @else
                At least two entries with recognised risk levels are needed to establish a trend.
            @endif
        </p>
    </x-dashboard.panel>

    <x-dashboard.panel title="Most frequent symptoms" class="lg:col-span-3">
        @if ($symptomCounts->isNotEmpty())
            <ul class="flex flex-wrap gap-2">
                @foreach ($symptomCounts as $symptom => $count)
                    <li class="rounded-full bg-secondary px-3 py-1.5 text-[12px] text-ink">
                        {{ str($symptom)->replace('_', ' ')->title() }}
                        <span class="text-ink-soft">&middot; {{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-[13px] text-ink-soft">No symptoms have been recorded for this patient yet.</p>
        @endif
    </x-dashboard.panel>

    <x-dashboard.panel title="Classification history" class="lg:col-span-3">
        @if ($results->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="text-[11px] tracking-[0.06em] text-ink-soft uppercase">
                            <th class="py-2 pr-4">Date</th>
                            <th class="py-2 pr-4">Risk level</th>
                            <th class="py-2 pr-4">Self-harm flag</th>
                            <th class="py-2 pr-4">PHQ-9</th>
                            <th class="py-2 pr-4">GAD-7</th>
                            <th class="py-2">Symptoms</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($results as $result)
                            <tr>
                                <td class="py-3 pr-4 whitespace-nowrap text-ink">
                                    @if ($result->ai_companion_session_id)
                                        <a href="{{ route("{$guardPrefix}.patients.conversations.show", [$patient, $result->ai_companion_session_id]) }}" class="text-teal-deep hover:underline">{{ $result->entry_date->format('j M Y') }}</a>
                                    @else
                                        {{ $result->entry_date->format('j M Y') }}
                                    @endif
                                </td>
                                <td class="py-3 pr-4"><x-dashboard.badge :status="$result->risk_level" /></td>
                                <td class="py-3 pr-4">
                                    @if ($result->self_harm_flag)
                                        <span class="font-semibold text-red-700">Yes</span>
                                    @else
                                        <span class="text-ink-soft">No</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 whitespace-nowrap text-ink">{{ str($result->phq9_severity ?? '—')->replace('_', ' ')->title() }}</td>
                                <td class="py-3 pr-4 whitespace-nowrap text-ink">{{ str($result->gad7_severity ?? '—')->replace('_', ' ')->title() }}</td>
                                <td class="py-3 text-ink-soft">{{ collect($result->symptoms ?? [])->map(fn ($symptom) => str($symptom)->replace('_', ' ')->title())->implode(', ') ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-[13px] text-ink-soft">No NLP classification results have been recorded for this patient yet.</p>
        @endif
    </x-dashboard.panel>
</div>
