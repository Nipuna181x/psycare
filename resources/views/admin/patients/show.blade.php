@extends('layouts.admin')

@php
    $title = 'Patient details';
    $subtitle = 'Account and care activity overview';
@endphp

@section('content')
    <a href="{{ route('admin.patients.index') }}" class="mb-4 inline-flex items-center gap-2 text-[12px] font-semibold text-ink hover:underline"><span aria-hidden="true">←</span> Back to Patients</a>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <div class="space-y-5">
            <x-dashboard.panel title="Patient account">
                <div class="flex items-center gap-4 border-b border-border pb-5">
                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-ink/10 font-display text-lg font-semibold text-ink">{{ collect(preg_split('/\s+/', trim($patient->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('') }}</div>
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h1 class="font-display text-xl font-medium text-ink">{{ $patient->name }}</h1><x-dashboard.badge :status="$patient->is_banned ? 'banned' : 'active'" /></div><p class="mt-1 text-[11px] text-ink-soft">Patient ID #{{ str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT) }}</p></div>
                </div>
                <dl class="mt-5 space-y-3 text-[12px]">
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Email</dt><dd class="break-all text-right font-medium text-ink">{{ $patient->email }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Mobile</dt><dd class="font-medium text-ink">{{ $patient->mobile }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Email verified</dt><dd class="font-medium text-ink">{{ $patient->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Registered</dt><dd class="font-medium text-ink">{{ $patient->created_at->format('M j, Y') }}</dd></div>
                    @if ($patient->banned_at)<div class="flex justify-between gap-4"><dt class="text-ink-soft">Suspended</dt><dd class="font-medium text-red-700">{{ $patient->banned_at->format('M j, Y g:i A') }}</dd></div>@endif
                </dl>
                <div class="mt-6 border-t border-border pt-5">
                    @if ($patient->is_banned)
                        <form method="POST" action="{{ route('admin.patients.restore', $patient) }}">@csrf @method('PATCH')<button class="w-full rounded-2xl bg-ink px-4 py-3 text-[12px] font-semibold text-white">Restore patient access</button></form>
                    @else
                        <form method="POST" action="{{ route('admin.patients.ban', $patient) }}" onsubmit="return confirm('Suspend this patient account? They will no longer be able to sign in.');">@csrf @method('PATCH')<button class="w-full rounded-2xl border border-red-200 px-4 py-3 text-[12px] font-semibold text-red-700 hover:bg-red-50">Suspend patient account</button></form>
                    @endif
                </div>
            </x-dashboard.panel>

            <div class="grid grid-cols-2 gap-3">
                <x-dashboard.stat-card label="Appointments" :value="$patient->appointments_count" chip="accent" accent="admin" />
                <x-dashboard.stat-card label="Mood entries" :value="$patient->mood_entries_count" chip="amber" />
                <x-dashboard.stat-card label="Prescriptions" :value="$patient->prescriptions_count" chip="emerald" />
                <x-dashboard.stat-card label="Group sessions" :value="$patient->therapy_room_participations_count" chip="rose" />
            </div>
        </div>

        <div class="space-y-5">
            <x-dashboard.panel title="Recent appointments" subtitle="Latest clinical bookings across the platform">
                <div class="space-y-3">
                    @forelse ($recentAppointments as $appointment)
                        <div class="rounded-2xl border border-border p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2"><div><p class="text-[13px] font-semibold text-ink">Dr. {{ $appointment->doctor?->name ?? 'Unavailable' }}</p><p class="mt-1 text-[11px] text-ink-soft">{{ $appointment->medicalCenter?->name ?? 'No medical center' }}</p></div><x-dashboard.badge :status="$appointment->status" /></div>
                            <p class="mt-3 text-[12px] text-ink-soft">{{ $appointment->appointment_date->format('M j, Y') }} · {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }} · {{ str_replace('_', ' ', ucfirst($appointment->mode)) }}</p>
                        </div>
                    @empty
                        <p class="rounded-2xl bg-secondary px-4 py-6 text-center text-[12px] text-ink-soft">No appointments recorded.</p>
                    @endforelse
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel title="Recent mood check-ins" subtitle="Last seven entries; admin view is read-only">
                <div class="space-y-3">
                    @forelse ($latestMoodEntries as $entry)
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-secondary px-4 py-3"><div><p class="text-[12px] font-semibold text-ink">{{ $entry->entry_date->format('M j, Y') }}</p><p class="mt-1 text-[11px] text-ink-soft">{{ collect($entry->mood_tags ?? [])->map(fn ($tag) => ucfirst($tag))->implode(', ') ?: 'No tags' }}</p></div><span class="grid h-9 w-9 place-items-center rounded-full bg-white text-[12px] font-bold text-ink">{{ $entry->mood_score }}/5</span></div>
                    @empty
                        <p class="rounded-2xl bg-secondary px-4 py-6 text-center text-[12px] text-ink-soft">No mood entries recorded.</p>
                    @endforelse
                </div>
            </x-dashboard.panel>
        </div>
    </div>
@endsection
