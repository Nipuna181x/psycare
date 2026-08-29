@extends('layouts.medical-center')

@php
    $title = 'Doctors';
    $subtitle = 'Your roster, pending requests, and doctor search';
    $tabs = [
        'my-doctors' => 'My Doctors',
        'pending' => 'Pending Requests',
        'search' => 'Search & Request',
    ];
@endphp

@section('content')
    <div class="mb-5 inline-flex rounded-full bg-secondary p-1">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('medical-center.doctors.index', ['tab' => $key]) }}" class="rounded-full px-5 py-2 text-[12px] font-medium transition-colors {{ $tab === $key ? 'bg-card text-ink shadow-[0_1px_0_0_var(--border)]' : 'text-ink-soft hover:text-ink' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($tab === 'my-doctors')
        @if ($myDoctors->isEmpty())
            <div class="rounded-3xl bg-card p-8 text-center">
                <p class="text-[13px] text-ink-soft">You don't have any active doctors yet.</p>
                <a href="{{ route('medical-center.doctors.index', ['tab' => 'search']) }}" class="mt-4 inline-flex rounded-xl bg-blue-800 px-5 py-2.5 text-[11px] font-semibold tracking-[0.08em] text-white uppercase hover:bg-blue-900">Search for doctors</a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($myDoctors as $affiliation)
                    @php $doctor = $affiliation->doctor; @endphp
                    <button type="button" onclick="document.getElementById('doctor-{{ $doctor->id }}').showModal()" class="rounded-3xl bg-card p-5 text-left shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] transition-transform hover:-translate-y-0.5">
                        <div class="flex items-center gap-3">
                            @if ($doctor->avatarUrl())
                                <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" class="h-12 w-12 rounded-2xl object-cover">
                            @else
                                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-[13px] font-semibold text-blue-800">{{ $doctor->initials() }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-[14px] font-medium text-ink">{{ $doctor->name }}</p>
                                <p class="mt-0.5 truncate text-[12px] text-ink-soft">{{ $doctor->specialization ?? 'General practice' }}</p>
                            </div>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-[12px]">
                            <div><dt class="text-ink-soft">Licence no.</dt><dd class="mt-0.5 font-medium text-ink">{{ $doctor->license_number }}</dd></div>
                            <div><dt class="text-ink-soft">Session fee</dt><dd class="mt-0.5 font-medium text-ink">{{ $doctor->isPriced() ? 'LKR '.number_format((float) $doctor->consultation_fee, 2) : '—' }}</dd></div>
                            <div class="col-span-2"><dt class="text-ink-soft">At your clinic</dt><dd class="mt-0.5 font-medium text-ink">{{ $doctor->appointments_count }} appointment(s)</dd></div>
                        </dl>
                        <span class="mt-4 inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-bold tracking-[0.08em] text-emerald-700 uppercase">Active</span>
                    </button>
                    <x-doctor.detail-panel :doctor="$doctor" context="my-doctors" :clinic-appointment-count="$doctor->appointments_count" />
                @endforeach
            </div>
        @endif
    @elseif ($tab === 'pending')
        <x-dashboard.panel title="Requests awaiting a response">
            <ul class="divide-y divide-border">
                @forelse ($pendingRequests as $affiliation)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-100 text-[12px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($affiliation->doctor->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $affiliation->doctor->name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $affiliation->doctor->specialization ?? 'General practice' }} · Sent {{ $affiliation->requested_by_clinic_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <x-dashboard.badge :status="$affiliation->status" />
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No pending requests right now. <a href="{{ route('medical-center.doctors.index', ['tab' => 'search']) }}" class="font-medium text-blue-800 underline-offset-4 hover:underline">Find doctors</a>.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <details class="mt-5 rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)]">
            <summary class="cursor-pointer text-[13px] font-medium text-ink">Recent activity</summary>
            <ul class="mt-4 divide-y divide-border">
                @forelse ($recentActivity as $affiliation)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-100 text-[12px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($affiliation->doctor->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $affiliation->doctor->name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">Responded {{ $affiliation->responded_by_doctor_at?->diffForHumans() }}</p>
                            </div>
                        </div>
                        <x-dashboard.badge :status="$affiliation->status" />
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No responses yet.</li>
                @endforelse
            </ul>
        </details>
    @else
        <div class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <form method="GET" action="{{ route('medical-center.doctors.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                <input type="hidden" name="tab" value="search">
                <label class="block">
                    <span class="text-[11px] font-medium text-ink-soft">Licence number</span>
                    <input type="text" name="license_number" value="{{ $filters['license_number'] }}" placeholder="SLMC-1234" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                <label class="block">
                    <span class="text-[11px] font-medium text-ink-soft">Doctor name</span>
                    <input type="text" name="name" value="{{ $filters['name'] }}" placeholder="Dr. Amaya Silva" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                <button type="submit" class="mt-1.5 self-end rounded-xl bg-blue-800 px-6 py-2.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Search</button>
            </form>
            <p class="mt-3 text-[11px] text-ink-soft">Search by licence number for an exact match, or by name if you don't have it handy.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($searchResults as $result)
                @php $doctor = $result['doctor']; $existing = $result['existingAffiliation']; @endphp
                <button type="button" onclick="document.getElementById('doctor-{{ $doctor->id }}').showModal()" class="rounded-3xl bg-card p-5 text-left shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] transition-transform hover:-translate-y-0.5">
                    <div class="flex items-center gap-3">
                        @if ($doctor->avatarUrl())
                            <img src="{{ $doctor->avatarUrl() }}" alt="{{ $doctor->name }}" class="h-12 w-12 rounded-2xl object-cover">
                        @else
                            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-blue-100 text-[13px] font-semibold text-blue-800">{{ $doctor->initials() }}</span>
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
                        <span class="mt-4 block rounded-xl bg-blue-800 px-4 py-2.5 text-center text-[11px] font-semibold tracking-[0.08em] text-white uppercase">View & request</span>
                    @endif
                </button>
                <x-doctor.detail-panel
                    :doctor="$doctor"
                    context="search"
                    :existing-affiliation="$existing"
                    :send-request-route="route('medical-center.doctors.request', $doctor)"
                />
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
    @endif
@endsection
