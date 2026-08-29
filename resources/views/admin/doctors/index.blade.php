@extends('layouts.admin')

@php
    $title = 'Doctors';
    $subtitle = 'Review applications and oversee the clinical network';
    $tabs = [
        'all' => ['label' => 'All doctors', 'count' => $statusCounts->sum()],
        'pending_approval' => ['label' => 'New applications', 'count' => $statusCounts['pending_approval'] ?? 0],
        'approved' => ['label' => 'Approved', 'count' => $statusCounts['approved'] ?? 0],
        'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected'] ?? 0],
        'suspended' => ['label' => 'Suspended', 'count' => $statusCounts['suspended'] ?? 0],
    ];
@endphp

@section('content')
    @error('approval')<div class="mb-5 rounded-2xl bg-red-50 px-4 py-3 text-[12px] text-red-700">{{ $message }}</div>@enderror

    <div class="rounded-3xl bg-card p-5 sm:p-6">
        <div class="flex flex-col gap-5">
            <div>
                <h2 class="font-display text-[15px] font-medium text-ink">Browse doctors</h2>
                <p class="mt-1 text-[11px] text-ink-soft">Review applications or inspect the complete doctor directory.</p>
            </div>

            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <nav class="flex flex-wrap gap-2" aria-label="Doctor status">
                    @foreach ($tabs as $key => $tab)
                        <a href="{{ route('admin.doctors.index', ['status' => $key, 'search' => $search ?: null]) }}" @class([
                            'inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-[12px] font-semibold transition',
                            'bg-ink text-primary-foreground shadow-sm' => $status === $key,
                            'bg-secondary text-ink-soft hover:text-ink' => $status !== $key,
                        ])>{{ $tab['label'] }} <span @class(['rounded-full px-2 py-0.5 text-[10px]', 'bg-white/20' => $status === $key, 'bg-white' => $status !== $key])>{{ $tab['count'] }}</span></a>
                    @endforeach
                </nav>
                <form method="GET" action="{{ route('admin.doctors.index') }}" class="flex w-full flex-col gap-2 sm:flex-row xl:max-w-xl">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <label class="relative flex-1"><span class="sr-only">Search doctors</span><svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg><input name="search" value="{{ $search }}" placeholder="Name, email, licence or specialty" class="w-full rounded-2xl border border-border bg-white py-2.5 pl-10 pr-4 text-[12px] outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10"></label>
                    <button class="shrink-0 rounded-2xl bg-ink px-5 py-2.5 text-[12px] font-semibold text-primary-foreground transition hover:bg-ink/90">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.doctors.index', ['status' => $status]) }}" class="shrink-0 rounded-2xl border border-border px-4 py-2.5 text-center text-[12px] font-semibold text-ink-soft hover:bg-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        @forelse ($doctors as $doctor)
            <article class="rounded-3xl bg-card p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-start gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-2xl bg-ink/10 font-display text-sm font-semibold text-ink">
                        @if ($doctor->avatarUrl())<img src="{{ $doctor->avatarUrl() }}" alt="" class="h-full w-full object-cover">@else{{ $doctor->initials() }}@endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2"><div><h2 class="font-display text-[16px] font-medium text-ink">Dr. {{ $doctor->name }}</h2><p class="mt-1 text-[11px] text-ink-soft">{{ $doctor->specialization ?: 'Specialization not added' }} · {{ $doctor->license_number }}</p></div><x-dashboard.badge :status="$doctor->status" /></div>
                        <div class="mt-4 grid gap-2 text-[12px] text-ink-soft sm:grid-cols-2"><p class="truncate">{{ $doctor->email }}</p><p class="truncate sm:text-right">{{ $doctor->phone ?: 'No phone added' }}</p></div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
                            <div class="flex flex-wrap gap-4 text-[11px] text-ink-soft"><span><strong class="text-ink">{{ $doctor->active_affiliations_count }}</strong> clinics</span><span><strong class="text-ink">{{ $doctor->appointments_count }}</strong> appointments</span><span>{{ str_replace('_', ' ', ucfirst($doctor->onboarding_step)) }}</span></div>
                            <div class="flex items-center gap-2">
                                @if ($doctor->status !== 'approved' && $doctor->onboarding_step === 'profile_complete')
                                    <form method="POST" action="{{ route('admin.doctors.approve', $doctor) }}">@csrf @method('PATCH')<button class="rounded-xl bg-emerald-100 px-3 py-2 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-200">Approve</button></form>
                                @endif
                                <a href="{{ route('admin.doctors.show', $doctor) }}" class="rounded-xl bg-ink px-3 py-2 text-[11px] font-semibold text-white">View details</a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-card px-6 py-14 text-center xl:col-span-2"><div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-secondary text-ink-soft"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></div><h2 class="mt-4 font-display text-[16px] font-medium text-ink">{{ $status === 'pending_approval' ? 'No new applications' : 'No doctors found' }}</h2><p class="mt-1 text-[12px] text-ink-soft">{{ $status === 'pending_approval' ? 'All completed doctor applications have been reviewed.' : 'Try another status or search term.' }}</p>@if ($status !== 'all')<a href="{{ route('admin.doctors.index') }}" class="mt-4 inline-flex rounded-xl bg-ink px-4 py-2 text-[11px] font-semibold text-primary-foreground">View all doctors</a>@endif</div>
        @endforelse
    </div>
    <div class="mt-5">{{ $doctors->links() }}</div>
@endsection
