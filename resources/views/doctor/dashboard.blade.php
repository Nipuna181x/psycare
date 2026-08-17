@extends('layouts.doctor')

@php
    $title = 'Dashboard';
    $subtitle = 'Welcome back, Dr. '.$doctor->name;
@endphp

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Specialisation" :value="$doctor->specialization ?? 'Not set'" chip="rose">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v6a4 4 0 0 0 8 0V2"/><circle cx="20" cy="10" r="2"/><path d="M20 12a2 2 0 0 0-2 2v2a6 6 0 0 1-6 6 6 6 0 0 1-6-6v-2a2 2 0 0 0-2-2"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Medical centre" :value="$doctor->medicalCenter->name ?? 'Unassigned'" chip="amber">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2"/><path d="M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card
            label="Account status"
            :value="ucfirst($doctor->status)"
            chip="emerald"
            :delta="$doctor->status === 'active' ? 'Visible to patients' : 'Hidden from patients'"
            :delta-tone="$doctor->status === 'active' ? 'positive' : 'neutral'"
        >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Member since" :value="$doctor->created_at->format('M Y')" chip="accent" accent="doctor">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
        </x-dashboard.stat-card>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Your profile" subtitle="How patients see you on PsyCare" class="lg:col-span-2">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Full name</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $doctor->name }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Email</dt>
                    <dd class="mt-1 truncate text-[13px] font-medium text-ink">{{ $doctor->email }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Phone</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $doctor->phone ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] text-ink-soft uppercase tracking-[0.08em]">Username</dt>
                    <dd class="mt-1 text-[13px] font-medium text-ink">{{ $doctor->username }}</dd>
                </div>
            </dl>
        </x-dashboard.panel>

        <x-dashboard.panel title="What's next" subtitle="Coming soon to your portal">
            <ul class="space-y-4">
                @foreach (['Appointment scheduling', 'Patient records', 'Session notes'] as $item)
                    <li class="flex items-center gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-sky-100 text-sky-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </span>
                        <span class="text-[13px] text-ink">{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        </x-dashboard.panel>
    </div>
@endsection
