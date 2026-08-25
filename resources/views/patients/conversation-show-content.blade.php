@php
    $guardPrefix = auth('doctor')->check() ? 'doctor' : 'admin';
    $result = $session->classificationResult;
@endphp

<div class="mb-5 flex items-start justify-between gap-4">
    <div>
        <h1 class="font-display text-[20px] font-medium text-ink">Conversation transcript</h1>
        <p class="mt-1 text-[13px] text-ink-soft">
            {{ $patient->name }} &middot; {{ $session->created_at->format('l, j M Y, g:i A') }}
            @if ($session->ended_at) &ndash; {{ $session->ended_at->format('g:i A') }} @endif
        </p>
    </div>
    <a href="{{ route("{$guardPrefix}.patients.conversations.index", $patient) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">Back to conversation history</a>
</div>

@if ($result?->self_harm_flag)
    <div class="mb-5 rounded-2xl border-2 border-red-400 bg-red-100 p-5 text-red-900">
        <p class="text-[12px] font-bold tracking-[0.08em] uppercase">⚠ Self-harm signal in this conversation</p>
        <p class="mt-2 text-[13px] leading-relaxed">
            This conversation was flagged for self-harm content.
            <strong>This is an automated screening signal only, not a standalone safety determination.</strong>
            It must be clinically reviewed against this transcript before any decision is made.
        </p>
    </div>
@endif

<div class="grid gap-5 lg:grid-cols-3">
    <x-dashboard.panel title="Risk at this moment">
        @if ($result)
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">Risk level</p>
                    <div class="mt-1"><x-dashboard.badge :status="$result->risk_level" /></div>
                </div>
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">Classified on</p>
                    <p class="mt-1 text-[13px] font-medium text-ink">{{ $result->entry_date->format('j M Y') }}</p>
                </div>
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">PHQ-9</p>
                    <p class="mt-1 text-[13px] font-medium text-ink">{{ str($result->phq9_severity ?? 'Not scored')->replace('_', ' ')->title() }}</p>
                </div>
                <div class="rounded-2xl bg-secondary p-3">
                    <p class="text-[11px] text-ink-soft">GAD-7</p>
                    <p class="mt-1 text-[13px] font-medium text-ink">{{ str($result->gad7_severity ?? 'Not scored')->replace('_', ' ')->title() }}</p>
                </div>
            </div>
            @if (! empty($result->symptoms))
                <div class="mt-4">
                    <p class="text-[11px] text-ink-soft">Symptoms detected in this conversation</p>
                    <ul class="mt-2 flex flex-wrap gap-2">
                        @foreach ($result->symptoms as $symptom)
                            <li class="rounded-full bg-secondary px-3 py-1 text-[11px] text-ink">{{ str($symptom)->replace('_', ' ')->title() }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <p class="text-[13px] text-ink-soft">This conversation has not been classified yet.</p>
            <a href="{{ route("{$guardPrefix}.patients.nlp-report.show", $patient) }}" class="mt-3 inline-block text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">Go sync it from the NLP report</a>
        @endif
    </x-dashboard.panel>

    <x-dashboard.panel title="Transcript" class="lg:col-span-2">
        <ul class="space-y-4">
            @foreach ($turns as $turn)
                <li class="flex {{ $turn->role === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-3 {{ $turn->role === 'user' ? 'bg-ink text-primary-foreground' : 'bg-secondary text-ink' }}">
                        <p class="text-[10px] font-semibold tracking-[0.08em] uppercase {{ $turn->role === 'user' ? 'text-primary-foreground/70' : 'text-ink-soft' }}">{{ $turn->role === 'user' ? $patient->name : 'Lumi' }}</p>
                        <p class="mt-1 text-[13px] leading-relaxed">{{ $turn->content }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </x-dashboard.panel>
</div>
