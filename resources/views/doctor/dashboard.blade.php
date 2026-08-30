@extends('layouts.doctor')

@php
    $title = 'Dashboard';
    $subtitle = 'Welcome back, Dr. '.$doctor->name;
@endphp

@section('content')
    @if ($noClinicAffiliation)
        <div class="rounded-3xl bg-card p-6 text-center shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-8">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-sky-100 text-sky-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2"/><path d="M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
            </span>
            <h2 class="mt-4 font-display text-[16px] font-medium text-ink">You're not currently affiliated with any clinic</h2>
            <p class="mx-auto mt-2 max-w-[48ch] text-[13px] leading-relaxed text-ink-soft">Complete your profile and wait for a clinic to send you a work request, or browse clinics to see who's active on PsyCare.</p>
            <a href="{{ route('doctor.clinic-requests.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-ink px-6 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">
                View clinic requests
            </a>
        </div>
    @endif

    @if ($noPriceSet)
        <div class="mt-5 rounded-3xl bg-card p-6 text-center shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-8">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-sky-100 text-sky-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <h2 class="mt-4 font-display text-[16px] font-medium text-ink">Set your session price to start receiving bookings</h2>
            <p class="mx-auto mt-2 max-w-[48ch] text-[13px] leading-relaxed text-ink-soft">Patients can't complete checkout until you set a consultation fee.</p>
            <a href="{{ route('doctor.profile.edit') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-ink px-6 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">
                Set your price
            </a>
        </div>
    @endif

    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Specialisation" :value="$doctor->specialization ?? 'Not set'" chip="rose">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v6a4 4 0 0 0 8 0V2"/><circle cx="20" cy="10" r="2"/><path d="M20 12a2 2 0 0 0-2 2v2a6 6 0 0 1-6 6 6 6 0 0 1-6-6v-2a2 2 0 0 0-2-2"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Active clinics" :value="$clinicId ? $doctor->activeAffiliations->firstWhere('clinic_id', $clinicId)?->clinic?->name : ($doctor->activeAffiliations->pluck('clinic.name')->implode(', ') ?: 'None yet')" chip="amber">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2"/><path d="M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card
            label="Account status"
            :value="ucfirst(str_replace('_', ' ', $doctor->status))"
            chip="emerald"
            :delta="$doctor->status === 'approved' ? 'Visible to patients' : 'Hidden from patients'"
            :delta-tone="$doctor->status === 'approved' ? 'positive' : 'neutral'"
        >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Member since" :value="$doctor->created_at->format('M Y')" chip="accent" accent="doctor">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
        </x-dashboard.stat-card>
    </div>

    <div class="mt-5 grid gap-5 sm:grid-cols-3">
        <x-dashboard.stat-card label="Today's appointments" :value="$todayCount" chip="rose">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
        </x-dashboard.stat-card>
        <x-dashboard.stat-card label="Upcoming appointments" :value="$upcomingCount" chip="accent" accent="doctor">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </x-dashboard.stat-card>
        <x-dashboard.stat-card label="Completed" :value="$completedCount" chip="emerald">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
        </x-dashboard.stat-card>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Next appointments" subtitle="Upcoming, soonest first" class="lg:col-span-2">
            <x-slot:action>
                <a href="{{ route('doctor.appointments.index') }}" class="inline-flex items-center gap-1.5 text-[12px] font-medium text-sky-700 transition-colors hover:text-sky-800">
                    View all
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                </a>
            </x-slot:action>
            <ul class="grid gap-3">
                @forelse ($nextAppointments as $appointment)
                    @include('doctor.appointments._row', ['appointment' => $appointment])
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No upcoming appointments yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <x-dashboard.panel title="Pre-assessment risk" subtitle="Across confirmed appointments">
            <x-dashboard.bar-list
                accent="doctor"
                empty-label="No confirmed appointments yet."
                :items="collect(['low' => 'Low', 'moderate' => 'Moderate', 'elevated' => 'Elevated'])->map(fn ($label, $key) => ['label' => $label, 'value' => $riskCounts[$key] ?? 0])->values()->all()"
            />
        </x-dashboard.panel>
    </div>
@endsection
