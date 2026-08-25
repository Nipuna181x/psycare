@php
    $guardPrefix = auth('doctor')->check() ? 'doctor' : 'admin';
@endphp

<div class="mb-5 flex items-start justify-between gap-4">
    <div>
        <h1 class="font-display text-[20px] font-medium text-ink">Conversation history</h1>
        <p class="mt-1 text-[13px] text-ink-soft">{{ $patient->name }} &middot; {{ $sessionsByDay->flatten()->count() }} {{ Str::plural('conversation', $sessionsByDay->flatten()->count()) }} across {{ $sessionsByDay->count() }} {{ Str::plural('day', $sessionsByDay->count()) }}</p>
    </div>
    <a href="{{ route("{$guardPrefix}.patients.nlp-report.show", $patient) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">Back to NLP report</a>
</div>

@if ($sessionsByDay->isEmpty())
    <x-dashboard.panel title="No conversations yet">
        <p class="text-[13px] text-ink-soft">This patient has not had a Lumi conversation yet.</p>
    </x-dashboard.panel>
@else
    <div class="space-y-5">
        @foreach ($sessionsByDay as $day => $sessions)
            @php($dayLabel = \Illuminate\Support\Carbon::parse($day)->format('l, j M Y'))
            <x-dashboard.panel :title="$dayLabel">
                <ul class="divide-y divide-border">
                    @foreach ($sessions as $session)
                        <li class="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
                            <div>
                                <p class="text-[13px] font-medium text-ink">
                                    {{ $session->created_at->format('g:i A') }}
                                    @if ($session->ended_at)
                                        &ndash; {{ $session->ended_at->format('g:i A') }}
                                    @else
                                        <span class="text-ink-soft">(in progress)</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-[12px] text-ink-soft">{{ $session->turns_count }} {{ Str::plural('message', $session->turns_count) }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($session->classificationResult)
                                    <x-dashboard.badge :status="$session->classificationResult->risk_level" />
                                    @if ($session->classificationResult->self_harm_flag)
                                        <span class="text-[10px] font-semibold tracking-[0.06em] text-red-700 uppercase">Self-harm flagged</span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-ink-soft">Not yet classified</span>
                                @endif
                                <a href="{{ route("{$guardPrefix}.patients.conversations.show", [$patient, $session]) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">View</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-dashboard.panel>
        @endforeach
    </div>
@endif
