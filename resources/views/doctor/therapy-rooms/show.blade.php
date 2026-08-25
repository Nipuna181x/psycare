@extends('layouts.doctor')

@php
    $title = $therapyRoom->title;
    $subtitle = $therapyRoom->scheduled_at->format('D, j M Y · g:i A').' · '.$therapyRoom->duration_minutes.' min';
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Session" class="lg:col-span-2">
            <x-slot:action>
                <x-dashboard.badge :status="$therapyRoom->status" />
            </x-slot:action>

            @if ($therapyRoom->topic)
                <p class="text-[13px] leading-relaxed text-ink">{{ $therapyRoom->topic }}</p>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-border pt-5">
                @if ($therapyRoom->status === 'scheduled')
                    <form method="POST" action="{{ route('doctor.therapy-rooms.start', $therapyRoom) }}">
                        @csrf
                        <button type="submit" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Start session</button>
                    </form>
                    <a href="{{ route('doctor.therapy-rooms.edit', $therapyRoom) }}" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Edit details</a>
                @elseif ($therapyRoom->status === 'live')
                    <a href="{{ route('doctor.therapy-rooms.session', $therapyRoom) }}" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Rejoin call</a>
                    <form method="POST" action="{{ route('doctor.therapy-rooms.end', $therapyRoom) }}">
                        @csrf
                        <button type="submit" class="rounded-2xl bg-red-100 px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-red-700 uppercase transition-transform hover:-translate-y-0.5">End session</button>
                    </form>
                @endif
            </div>
        </x-dashboard.panel>

        <x-dashboard.panel title="Participants" :subtitle="$therapyRoom->activeParticipants->count().' of '.\App\Models\TherapyRoom::MAX_PARTICIPANTS">
            <ul class="divide-y divide-border">
                @forelse ($therapyRoom->activeParticipants as $participant)
                    <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $participant->patient->name }}</p>
                            <p class="mt-0.5 text-[11px] text-ink-soft">{{ $participant->anonymous_label }}</p>
                        </div>
                        @if ($therapyRoom->isEditable())
                            <form method="POST" action="{{ route('doctor.therapy-rooms.participants.destroy', [$therapyRoom, $participant]) }}" onsubmit="return confirm('Remove {{ $participant->patient->name }} from this session? They will be notified.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-semibold text-red-600 hover:underline">Remove</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No patients assigned yet.</li>
                @endforelse
            </ul>

            @if ($therapyRoom->isEditable() && $therapyRoom->activeParticipants->count() < \App\Models\TherapyRoom::MAX_PARTICIPANTS)
                <a href="{{ route('doctor.therapy-rooms.edit', $therapyRoom) }}" class="mt-4 block text-center text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">Add a patient</a>
            @endif
        </x-dashboard.panel>
    </div>
@endsection
