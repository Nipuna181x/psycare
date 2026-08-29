@extends('layouts.admin')

@php
    $title = 'Medical Centers';
    $subtitle = 'Review applications and manage approved care providers';
    $tabs = [
        'all' => ['label' => 'All centers', 'count' => $statusCounts->sum()],
        'pending' => ['label' => 'New applications', 'count' => $statusCounts['pending'] ?? 0],
        'approved' => ['label' => 'Approved', 'count' => $statusCounts['approved'] ?? 0],
        'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected'] ?? 0],
    ];
@endphp

@section('content')
    <div class="rounded-3xl bg-card p-5 sm:p-6">
        <div class="flex flex-col gap-5">
            <div>
                <h2 class="font-display text-[15px] font-medium text-ink">Browse medical centers</h2>
                <p class="mt-1 text-[11px] text-ink-soft">Switch between all registrations and approval states.</p>
            </div>

            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <nav class="flex flex-wrap gap-2" aria-label="Medical center status">
                    @foreach ($tabs as $key => $tab)
                        <a href="{{ route('admin.medical-centers.index', ['status' => $key, 'search' => $search ?: null]) }}"
                           @class([
                               'inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-[12px] font-semibold transition',
                               'bg-ink text-primary-foreground shadow-sm' => $status === $key,
                               'bg-secondary text-ink-soft hover:text-ink' => $status !== $key,
                           ])>
                            {{ $tab['label'] }}
                            <span @class(['rounded-full px-2 py-0.5 text-[10px]', 'bg-white/20' => $status === $key, 'bg-white' => $status !== $key])>{{ $tab['count'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('admin.medical-centers.index') }}" class="flex w-full flex-col gap-2 sm:flex-row xl:max-w-xl">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <label class="relative flex-1">
                        <span class="sr-only">Search medical centers</span>
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input name="search" value="{{ $search }}" placeholder="Name, email, registration or address"
                               class="w-full rounded-2xl border border-border bg-white py-2.5 pl-10 pr-4 text-[12px] text-ink outline-none transition focus:border-ink/50 focus:ring-2 focus:ring-ink/10">
                    </label>
                    <button class="shrink-0 rounded-2xl bg-ink px-5 py-2.5 text-[12px] font-semibold text-primary-foreground transition hover:bg-ink/90">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.medical-centers.index', ['status' => $status]) }}" class="shrink-0 rounded-2xl border border-border px-4 py-2.5 text-center text-[12px] font-semibold text-ink-soft hover:bg-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        @forelse ($medicalCenters as $medicalCenter)
            <article class="group rounded-3xl bg-card p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-start gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-ink/10 text-ink">
                        @if ($medicalCenter->logoUrl())
                            <img src="{{ $medicalCenter->logoUrl() }}" alt="" class="h-full w-full object-cover">
                        @else
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="min-w-0">
                                <h2 class="truncate font-display text-[16px] font-medium text-ink">{{ $medicalCenter->name }}</h2>
                                <p class="mt-1 truncate text-[11px] text-ink-soft">{{ $medicalCenter->registration_number }} · Joined {{ $medicalCenter->created_at->format('M j, Y') }}</p>
                            </div>
                            <x-dashboard.badge :status="$medicalCenter->status" />
                        </div>
                        <div class="mt-4 grid gap-2 text-[12px] text-ink-soft sm:grid-cols-2">
                            <p class="truncate">{{ $medicalCenter->email }}</p>
                            <p class="truncate sm:text-right">{{ $medicalCenter->phone }}</p>
                            <p class="truncate sm:col-span-2">{{ $medicalCenter->address }}</p>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
                            <div class="flex gap-4 text-[11px] text-ink-soft">
                                <span><strong class="text-ink">{{ $medicalCenter->affiliated_doctors_count }}</strong> doctors</span>
                                <span><strong class="text-ink">{{ $medicalCenter->appointments_count }}</strong> appointments</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($medicalCenter->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.medical-centers.approve', $medicalCenter) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-xl bg-emerald-100 px-3 py-2 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-200">Approve</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.medical-centers.show', $medicalCenter) }}" class="rounded-xl bg-ink px-3 py-2 text-[11px] font-semibold text-white">View details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-card px-6 py-14 text-center xl:col-span-2">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-secondary text-ink-soft">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M10 10h4M10 14h4"/></svg>
                </div>
                <h2 class="mt-4 font-display text-[16px] font-medium text-ink">{{ $status === 'pending' ? 'No new applications' : 'No medical centers found' }}</h2>
                <p class="mt-1 text-[12px] text-ink-soft">{{ $status === 'pending' ? 'All medical center applications have been reviewed.' : 'Try another status or search term.' }}</p>
                @if ($status !== 'all')
                    <a href="{{ route('admin.medical-centers.index') }}" class="mt-4 inline-flex rounded-xl bg-ink px-4 py-2 text-[11px] font-semibold text-primary-foreground">View all centers</a>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $medicalCenters->links() }}</div>
@endsection
