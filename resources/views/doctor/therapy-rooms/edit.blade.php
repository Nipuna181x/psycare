@extends('layouts.doctor')

@php
    $title = 'Edit '.$therapyRoom->title;
    $subtitle = 'Only editable while the session is still scheduled';
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Session details" class="lg:col-span-2">
            <form method="POST" action="{{ route('doctor.therapy-rooms.update', $therapyRoom) }}" class="grid gap-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="title" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $therapyRoom->title) }}" required class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                    @error('title')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="topic" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Topic (optional)</label>
                    <textarea name="topic" id="topic" rows="3" class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">{{ old('topic', $therapyRoom->topic) }}</textarea>
                    @error('topic')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="scheduled_at" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Date & time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at', $therapyRoom->scheduled_at->format('Y-m-d\TH:i')) }}" required class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                        @error('scheduled_at')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="duration_minutes" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', $therapyRoom->duration_minutes) }}" min="15" max="240" required class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                        @error('duration_minutes')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <button type="submit" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Save changes</button>
                </div>
            </form>
        </x-dashboard.panel>

        <x-dashboard.panel title="Add a patient" subtitle="From your appointment history">
            <form method="POST" action="{{ route('doctor.therapy-rooms.participants.store', $therapyRoom) }}" class="grid gap-3">
                @csrf
                <select name="patient_id" required class="w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                    <option value="">Choose a patient…</option>
                    @foreach ($patients as $patient)
                        @unless ($therapyRoom->activeParticipants->contains('patient_id', $patient->id))
                            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                        @endunless
                    @endforeach
                </select>
                @error('patient_id')<p class="text-[11px] text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="rounded-2xl bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Add & notify</button>
            </form>
        </x-dashboard.panel>
    </div>
@endsection
