@extends('layouts.admin')

@php
    $title = 'Medical center details';
    $subtitle = 'Registration, network and appointment overview';
@endphp

@section('content')
    <a href="{{ route('admin.medical-centers.index') }}" class="mb-4 inline-flex items-center gap-2 text-[12px] font-semibold text-ink hover:underline">← Back to Medical Centers</a>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]">
        <div class="space-y-5">
            <x-dashboard.panel title="Organization profile">
                <div class="flex items-center gap-4 border-b border-border pb-5">
                    <div class="grid h-14 w-14 place-items-center overflow-hidden rounded-2xl bg-ink/10 text-ink">
                        @if ($medicalCenter->logoUrl())<img src="{{ $medicalCenter->logoUrl() }}" alt="" class="h-full w-full object-cover">@else<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>@endif
                    </div>
                    <div><div class="flex flex-wrap items-center gap-2"><h1 class="font-display text-xl font-medium text-ink">{{ $medicalCenter->name }}</h1><x-dashboard.badge :status="$medicalCenter->status" /></div><p class="mt-1 text-[11px] text-ink-soft">{{ $medicalCenter->registration_number }}</p></div>
                </div>
                <dl class="mt-5 space-y-3 text-[12px]">
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Email</dt><dd class="break-all text-right font-medium text-ink">{{ $medicalCenter->email }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Phone</dt><dd class="font-medium text-ink">{{ $medicalCenter->phone }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Address</dt><dd class="max-w-64 text-right font-medium text-ink">{{ $medicalCenter->address }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Facility fee</dt><dd class="font-medium text-ink">LKR {{ number_format((float) $medicalCenter->facility_fee, 2) }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-ink-soft">Registered</dt><dd class="font-medium text-ink">{{ $medicalCenter->created_at->format('M j, Y') }}</dd></div>
                </dl>
                @if ($medicalCenter->description)<p class="mt-5 border-t border-border pt-5 text-[12px] leading-6 text-ink-soft">{{ $medicalCenter->description }}</p>@endif
                <div class="mt-6 flex gap-2 border-t border-border pt-5">
                    @if ($medicalCenter->status !== 'approved')<form method="POST" action="{{ route('admin.medical-centers.approve', $medicalCenter) }}" class="flex-1">@csrf @method('PATCH')<button class="w-full rounded-2xl bg-ink px-4 py-3 text-[12px] font-semibold text-white">Approve</button></form>@endif
                    @if ($medicalCenter->status !== 'rejected')<form method="POST" action="{{ route('admin.medical-centers.reject', $medicalCenter) }}" class="flex-1" onsubmit="return confirm('Reject this medical center?');">@csrf @method('PATCH')<button class="w-full rounded-2xl border border-red-200 px-4 py-3 text-[12px] font-semibold text-red-700 hover:bg-red-50">Reject</button></form>@endif
                </div>
            </x-dashboard.panel>
            <div class="grid grid-cols-2 gap-3"><x-dashboard.stat-card label="Doctors" :value="$medicalCenter->affiliated_doctors_count" chip="accent" accent="admin"/><x-dashboard.stat-card label="Appointments" :value="$medicalCenter->appointments_count" chip="emerald"/><x-dashboard.stat-card label="Staff" :value="$medicalCenter->staff_count" chip="amber"/><x-dashboard.stat-card label="Processed" :value="'LKR '.number_format((float) $totalRevenue, 0)" chip="rose"/></div>
        </div>

        <div class="space-y-5">
            <x-dashboard.panel title="Affiliated doctors" subtitle="Current active clinical network">
                <div class="space-y-3">@forelse ($doctors as $doctor)<a href="{{ route('admin.doctors.show', $doctor) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-border px-4 py-3 transition hover:border-ink/30 hover:bg-ink/5"><div><p class="text-[12px] font-semibold text-ink">Dr. {{ $doctor->name }}</p><p class="mt-1 text-[11px] text-ink-soft">{{ $doctor->specialization ?: 'Unspecified' }} · {{ $doctor->license_number }}</p></div><span class="text-ink">→</span></a>@empty<p class="rounded-2xl bg-secondary px-4 py-6 text-center text-[12px] text-ink-soft">No active doctor affiliations.</p>@endforelse</div>
            </x-dashboard.panel>
            <x-dashboard.panel title="Recent appointments" subtitle="Latest platform activity at this center">
                <div class="space-y-3">@forelse ($recentAppointments as $appointment)<div class="rounded-2xl border border-border p-4"><div class="flex flex-wrap items-start justify-between gap-2"><div><p class="text-[12px] font-semibold text-ink">{{ $appointment->user?->name ?? $appointment->patient_name }}</p><p class="mt-1 text-[11px] text-ink-soft">Dr. {{ $appointment->doctor?->name ?? 'Unavailable' }}</p></div><x-dashboard.badge :status="$appointment->status" /></div><p class="mt-3 text-[11px] text-ink-soft">{{ $appointment->appointment_date->format('M j, Y') }} · {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p></div>@empty<p class="rounded-2xl bg-secondary px-4 py-6 text-center text-[12px] text-ink-soft">No appointments recorded.</p>@endforelse</div>
            </x-dashboard.panel>
        </div>
    </div>
@endsection
