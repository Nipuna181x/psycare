@extends('layouts.doctor')

@php
    $title = 'Schedule a Group Session';
    $subtitle = 'Assign patients and they\'ll be notified with their own anonymous label';
@endphp

@section('content')
    <x-dashboard.panel title="Session details">
        <form method="POST" action="{{ route('doctor.therapy-rooms.store') }}" class="grid gap-5">
            @csrf

            <div>
                <label for="title" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                @error('title')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="topic" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Topic (optional)</label>
                <textarea name="topic" id="topic" rows="3" class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">{{ old('topic') }}</textarea>
                @error('topic')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="scheduled_at" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Date & time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" required class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                    @error('scheduled_at')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="duration_minutes" class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" id="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="15" max="240" required class="mt-1.5 w-full rounded-2xl border border-border bg-card px-4 py-3 text-[13px] text-ink">
                    @error('duration_minutes')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Patients (max {{ $maxParticipants }} — full-mesh video calls degrade past this size)</label>
                <div class="mt-2 grid gap-2 rounded-2xl border border-border bg-card p-3 sm:grid-cols-2">
                    @forelse ($patients as $patient)
                        <label class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-[13px] text-ink hover:bg-secondary">
                            <input type="checkbox" name="patient_ids[]" value="{{ $patient->id }}" @checked(in_array($patient->id, old('patient_ids', [])))>
                            {{ $patient->name }}
                        </label>
                    @empty
                        <p class="col-span-2 py-2 text-[12px] text-ink-soft">You don't have any patients from past appointments yet.</p>
                    @endforelse
                </div>
                @error('patient_ids')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
                @error('patient_ids.*')<p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <button type="submit" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Schedule & notify patients</button>
            </div>
        </form>
    </x-dashboard.panel>
@endsection
