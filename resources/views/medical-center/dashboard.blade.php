@extends('layouts.medical-center')

@php
    $title = 'Dashboard';
    $subtitle = 'Your clinic at a glance';
@endphp

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Doctors on roster" :value="$totalDoctors" chip="rose">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Active doctors" :value="$activeDoctors" chip="emerald">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card
            label="Inactive doctors"
            :value="$inactiveDoctors"
            chip="amber"
            :delta="$inactiveDoctors > 0 ? 'Review their status' : null"
            delta-tone="neutral"
        >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Specialisations offered" :value="$specializationCount" chip="accent" accent="clinic">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v6a4 4 0 0 0 8 0V2"/><circle cx="20" cy="10" r="2"/><path d="M20 12a2 2 0 0 0-2 2v2a6 6 0 0 1-6 6 6 6 0 0 1-6-6v-2a2 2 0 0 0-2-2"/></svg>
        </x-dashboard.stat-card>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Doctors by status" subtitle="Active vs. inactive on your roster" class="lg:col-span-2">
            <x-dashboard.column-chart
                accent="clinic"
                :items="[
                    ['label' => 'Active', 'value' => $statusCounts['active']],
                    ['label' => 'Inactive', 'value' => $statusCounts['inactive'], 'tone' => 'muted'],
                ]"
            />
        </x-dashboard.panel>

        <x-dashboard.panel title="Specialisations" subtitle="Coverage across your roster">
            <x-dashboard.bar-list
                accent="clinic"
                empty-label="Add doctors to see coverage here."
                :items="$specializations->map(fn ($row) => ['label' => $row->specialization, 'value' => $row->total])->all()"
            />
        </x-dashboard.panel>
    </div>

    <div class="mt-5 grid gap-5">
        <x-dashboard.panel title="Recently added doctors" subtitle="Newest first">
            <x-slot:action>
                <a href="{{ route('medical-center.doctor-managment.index') }}" class="inline-flex items-center gap-1.5 text-[12px] font-medium text-purple-700 transition-colors hover:text-purple-800">
                    View all
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                </a>
            </x-slot:action>

            <ul class="divide-y divide-border">
                @forelse ($recentDoctors as $doctor)
                    <li class="flex items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-purple-100 text-[12px] font-semibold text-purple-700">{{ mb_strtoupper(mb_substr($doctor->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $doctor->name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $doctor->specialization ?? 'No specialisation set' }}</p>
                            </div>
                        </div>
                        <x-dashboard.badge :status="$doctor->status" />
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No doctors added yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>
    </div>
@endsection
