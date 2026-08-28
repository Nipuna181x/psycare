@extends('layouts.medical-center')

@php
    $title = 'Find Doctors';
    $subtitle = 'Search approved doctors and send them a work request';
@endphp

@section('content')
    <div class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
        <form method="GET" action="{{ route('medical-center.find-doctors.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Licence number</span>
                <input type="text" name="license_number" value="{{ $filters['license_number'] }}" placeholder="SLMC-1234" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100">
            </label>
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Doctor name</span>
                <input type="text" name="name" value="{{ $filters['name'] }}" placeholder="Dr. Amaya Silva" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100">
            </label>
            <button type="submit" class="mt-1.5 self-end rounded-xl bg-purple-700 px-6 py-2.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-purple-800">Search</button>
        </form>
        <p class="mt-3 text-[11px] text-ink-soft">Search by licence number for an exact match, or by name if you don't have it handy.</p>
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($results as $result)
            @php $doctor = $result['doctor']; $existing = $result['existingAffiliation']; @endphp
            <div class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)]">
                <div class="flex items-center gap-3">
                    @if ($doctor->avatarUrl())
                        <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" class="h-12 w-12 rounded-2xl object-cover">
                    @else
                        <span class="grid h-12 w-12 place-items-center rounded-2xl bg-purple-100 text-[13px] font-semibold text-purple-700">{{ $doctor->initials() }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-[14px] font-medium text-ink">{{ $doctor->name }}</p>
                        <p class="mt-0.5 truncate text-[12px] text-ink-soft">{{ $doctor->specialization ?? 'General practice' }}</p>
                    </div>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 text-[12px]">
                    <div><dt class="text-ink-soft">Licence no.</dt><dd class="mt-0.5 font-medium text-ink">{{ $doctor->license_number }}</dd></div>
                    <div><dt class="text-ink-soft">Experience</dt><dd class="mt-0.5 font-medium text-ink">{{ $doctor->years_of_experience ? $doctor->years_of_experience.'+ yrs' : '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-ink-soft">Current affiliations</dt><dd class="mt-0.5 font-medium text-ink">{{ $doctor->active_affiliations_count }} clinic(s)</dd></div>
                </dl>

                @if ($existing)
                    <span class="mt-4 block rounded-xl bg-secondary px-4 py-2.5 text-center text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">
                        {{ match ($existing->status) {
                            'requested' => 'Request pending',
                            'active' => 'Already affiliated',
                            'declined' => 'Request declined',
                            default => ucfirst($existing->status),
                        } }}
                    </span>
                @else
                    <form method="POST" action="{{ route('medical-center.find-doctors.request', $doctor) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-purple-700 px-4 py-2.5 text-[11px] font-semibold tracking-[0.08em] text-white uppercase hover:bg-purple-800">Send Work Request</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="col-span-full rounded-3xl bg-card p-8 text-center text-[13px] text-ink-soft">
                @if ($filters['license_number'] || $filters['name'])
                    No approved doctors matched your search.
                @else
                    Search by licence number or name to find doctors to work with.
                @endif
            </p>
        @endforelse
    </div>
@endsection
