@extends('layouts.admin')

@php
    $title = 'Dashboard';
    $subtitle = 'Platform overview and approvals';
@endphp

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Medical centres" :value="$totalMedicalCenters" chip="rose">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2"/><path d="M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card
            label="Pending approvals"
            :value="$pendingApprovalsCount"
            chip="amber"
            :delta="$pendingApprovalsCount > 0 ? 'Needs your review' : 'All caught up'"
            :delta-tone="$pendingApprovalsCount > 0 ? 'neutral' : 'positive'"
        >
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Registered doctors" :value="$totalDoctors" chip="emerald">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </x-dashboard.stat-card>

        <x-dashboard.stat-card label="Registered patients" :value="$totalPatients" chip="accent" accent="admin">
            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </x-dashboard.stat-card>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <x-dashboard.panel title="Medical centres by status" subtitle="Approved, pending and rejected registrations" class="lg:col-span-2">
            <x-dashboard.column-chart
                accent="admin"
                :items="[
                    ['label' => 'Approved', 'value' => $statusCounts['approved']],
                    ['label' => 'Pending', 'value' => $statusCounts['pending'], 'tone' => 'muted'],
                    ['label' => 'Rejected', 'value' => $statusCounts['rejected'], 'tone' => 'muted'],
                ]"
            />
        </x-dashboard.panel>

        <x-dashboard.panel title="Pending approvals" subtitle="Newest registrations first">
            <ul class="space-y-4">
                @forelse ($pendingCenters as $center)
                    <li class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $center->name }}</p>
                            <p class="mt-0.5 text-[11px] text-ink-soft">{{ $center->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1.5">
                            <form method="POST" action="{{ route('admin.medical-centers.approve', $center) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="grid h-8 w-8 place-items-center rounded-full bg-emerald-100 text-emerald-700 transition-colors hover:bg-emerald-200" title="Approve">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.medical-centers.reject', $center) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="grid h-8 w-8 place-items-center rounded-full bg-red-100 text-red-700 transition-colors hover:bg-red-200" title="Reject">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="text-[12px] text-ink-soft">Nothing waiting on you right now.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        <x-dashboard.panel title="Doctors by specialisation" subtitle="Top specialisations across the platform">
            <x-dashboard.bar-list
                accent="admin"
                empty-label="No doctors registered yet."
                :items="$specializations->map(fn ($row) => ['label' => $row->specialization, 'value' => $row->total])->all()"
            />
        </x-dashboard.panel>

        <x-dashboard.panel title="Recently registered" subtitle="Latest medical centre applications">
            <ul class="space-y-4">
                @forelse ($recentCenters as $center)
                    <li class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $center->name }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $center->email }}</p>
                        </div>
                        <x-dashboard.badge :status="$center->status" />
                    </li>
                @empty
                    <li class="text-[12px] text-ink-soft">No medical centres yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>
    </div>
@endsection
