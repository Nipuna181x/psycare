@extends('layouts.doctor')

@php
    $title = $therapyRoom->title;
    $subtitle = $therapyRoom->scheduled_at->format('D, j M Y · g:i A').' · '.$therapyRoom->duration_minutes.' min';
    $statusLabel = match ($therapyRoom->status) {
        'scheduled' => 'Upcoming',
        'live' => 'Live',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default => ucfirst($therapyRoom->status),
    };
    $statusTone = match ($therapyRoom->status) {
        'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'live' => 'bg-red-50 text-red-700 ring-red-100',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-secondary text-ink-soft ring-border',
    };
@endphp

@section('content')
    <div class="grid gap-5">
        <nav aria-label="Breadcrumb">
            <a href="{{ route('doctor.therapy-rooms.index') }}" class="inline-flex items-center gap-2 text-[12px] font-medium text-sky-700 transition-colors hover:text-sky-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-sky-500">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                Back to Group Sessions
            </a>
        </nav>

        <header class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold tracking-[0.12em] text-sky-700 uppercase">Anonymous group session</p>
                        <h1 class="mt-1 font-display text-[clamp(1.4rem,3vw,2rem)] font-medium tracking-tight text-ink">{{ $therapyRoom->title }}</h1>
                        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-ink-soft">
                            <span>{{ $therapyRoom->scheduled_at->format('D, j M Y') }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ $therapyRoom->scheduled_at->format('g:i A') }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ $therapyRoom->duration_minutes }} minutes</span>
                        </div>
                    </div>
                </div>
                <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold tracking-[0.08em] uppercase ring-1 ring-inset {{ $statusTone }}">
                    @if ($therapyRoom->status === 'live')
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-60 motion-reduce:animate-none"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-red-600"></span>
                        </span>
                    @endif
                    {{ $statusLabel }}
                </span>
            </div>

            @if ($therapyRoom->topic)
                <div class="mt-5 border-t border-border pt-5">
                    <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Session topic</p>
                    <p class="mt-2 max-w-[90ch] text-[13px] leading-relaxed text-slate-700">{{ $therapyRoom->topic }}</p>
                </div>
            @endif
        </header>

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,0.65fr)]">
            <div class="grid gap-5">
                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="session-details-heading">
                    <div class="border-b border-border pb-4">
                        <h2 id="session-details-heading" class="font-display text-[16px] font-medium text-ink">Session details</h2>
                        <p class="mt-0.5 text-[12px] text-ink-soft">Operational information for this facilitated room.</p>
                    </div>

                    <dl class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ([
                            ['label' => 'Date', 'value' => $therapyRoom->scheduled_at->format('D, j M Y'), 'icon' => 'calendar'],
                            ['label' => 'Time', 'value' => $therapyRoom->scheduled_at->format('g:i A'), 'icon' => 'clock'],
                            ['label' => 'Duration', 'value' => $therapyRoom->duration_minutes.' minutes', 'icon' => 'duration'],
                            ['label' => 'Maximum capacity', 'value' => \App\Models\TherapyRoom::MAX_PARTICIPANTS.' participants', 'icon' => 'capacity'],
                            ['label' => 'Current participants', 'value' => $therapyRoom->activeParticipants->count().' assigned', 'icon' => 'participants'],
                        ] as $detail)
                            <div class="rounded-2xl border border-border bg-white p-4">
                                <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">{{ $detail['label'] }}</dt>
                                <dd class="mt-1.5 text-[13px] font-medium text-ink">{{ $detail['value'] }}</dd>
                            </div>
                        @endforeach

                        <div class="rounded-2xl border border-border bg-white p-4">
                            <dt class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Mode</dt>
                            {{-- TODO: Add a persisted session mode field before displaying video, audio, or text. --}}
                            <dd class="mt-1.5 text-[13px] font-medium text-ink-soft">Not specified</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="participants-heading">
                    <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                        <div>
                            <h2 id="participants-heading" class="font-display text-[16px] font-medium text-ink">Anonymous participants</h2>
                            <p class="mt-0.5 text-[12px] text-ink-soft">Only assigned aliases are shown in this clinical view.</p>
                        </div>
                        <span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft">{{ $therapyRoom->activeParticipants->count() }} / {{ \App\Models\TherapyRoom::MAX_PARTICIPANTS }}</span>
                    </div>

                    <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                        @forelse ($therapyRoom->activeParticipants as $participant)
                            <li class="flex items-center justify-between gap-3 rounded-2xl border border-border bg-white p-3.5">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-secondary text-[11px] font-semibold text-ink-soft">{{ $loop->iteration }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-[12px] font-semibold text-ink">{{ $participant->anonymous_label }}</p>
                                        <p class="mt-0.5 text-[10px] text-ink-soft">Identity protected</p>
                                    </div>
                                </div>
                                @if ($therapyRoom->isEditable())
                                    <form method="POST" action="{{ route('doctor.therapy-rooms.participants.destroy', [$therapyRoom, $participant]) }}" onsubmit="return confirm('Remove {{ $participant->anonymous_label }} from this session? They will be notified.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Remove {{ $participant->anonymous_label }}" class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </li>
                        @empty
                            <li class="grid min-h-32 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-5 py-7 text-center sm:col-span-2">
                                <div>
                                    <svg class="mx-auto h-5 w-5 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                                    <p class="mt-2 text-[12px] font-medium text-ink">No participants assigned yet.</p>
                                    <p class="mt-1 text-[11px] text-ink-soft">Add eligible patients before the room goes live.</p>
                                </div>
                            </li>
                        @endforelse
                    </ul>

                    @if ($therapyRoom->isEditable() && $therapyRoom->activeParticipants->count() < \App\Models\TherapyRoom::MAX_PARTICIPANTS)
                        <a href="{{ route('doctor.therapy-rooms.edit', $therapyRoom) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-[10px] font-semibold tracking-[0.08em] text-sky-700 uppercase transition-colors hover:bg-sky-100 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            Add a participant
                        </a>
                    @endif
                </section>

                @if ($therapyRoom->status === 'completed')
                    <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="session-notes-heading">
                        <div class="flex items-center justify-between gap-3">
                            <h2 id="session-notes-heading" class="font-display text-[16px] font-medium text-ink">Session notes</h2>
                            <span class="rounded-full bg-secondary px-2.5 py-1 text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Unavailable</span>
                        </div>
                        {{-- TODO: Add a persisted session notes field before enabling note entry and save controls. --}}
                        <div class="mt-4 rounded-2xl border border-dashed border-border bg-secondary/40 p-4">
                            <p class="text-[12px] leading-relaxed text-ink-soft">Session notes are not available because this room has no notes field.</p>
                        </div>
                    </section>
                @elseif ($therapyRoom->status === 'cancelled')
                    <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="cancellation-heading">
                        <h2 id="cancellation-heading" class="font-display text-[16px] font-medium text-ink">Cancellation details</h2>
                        {{-- TODO: Add a persisted cancellation reason field before showing a reason here. --}}
                        <p class="mt-3 text-[12px] text-ink-soft">No cancellation reason is stored for this room.</p>
                    </section>
                @endif
            </div>

            <aside class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="session-actions-heading">
                <h2 id="session-actions-heading" class="font-display text-[16px] font-medium text-ink">Session actions</h2>
                <p class="mt-1 text-[11px] leading-relaxed text-ink-soft">Actions are limited by the room's current status.</p>

                <div class="mt-5 grid gap-2.5">
                    @if ($therapyRoom->status === 'scheduled')
                        <form method="POST" action="{{ route('doctor.therapy-rooms.start', $therapyRoom) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">Start session</button>
                        </form>
                        <a href="{{ route('doctor.therapy-rooms.edit', $therapyRoom) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-sky-200 bg-white px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-sky-700 uppercase transition-colors hover:bg-sky-50 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">Edit details</a>
                        {{-- TODO: Add an authorized cancellation endpoint before enabling this control. --}}
                        <button type="button" disabled title="Session cancellation is not available yet" class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-slate-400 uppercase">Cancel session</button>
                    @elseif ($therapyRoom->status === 'live')
                        <a href="{{ route('doctor.therapy-rooms.session', $therapyRoom) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">Rejoin call</a>
                        <form method="POST" action="{{ route('doctor.therapy-rooms.end', $therapyRoom) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl border border-red-200 bg-white px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-red-700 uppercase transition-colors hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-red-500">End session</button>
                        </form>
                    @else
                        <div class="rounded-2xl border border-border bg-secondary/40 p-4 text-center">
                            <svg class="mx-auto h-4 w-4 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            <p class="mt-2 text-[11px] leading-relaxed text-ink-soft">This session is read-only because it is {{ $therapyRoom->status }}.</p>
                        </div>
                    @endif
                </div>

                <div class="mt-5 border-t border-border pt-5">
                    <p class="text-[10px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Privacy reminder</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-ink-soft">Use anonymous participant labels during the session and avoid recording identifying details in shared spaces.</p>
                </div>
            </aside>
        </div>
    </div>
@endsection
