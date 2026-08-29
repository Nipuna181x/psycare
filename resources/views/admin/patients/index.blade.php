@extends('layouts.admin')

@php
    $title = 'Patients';
    $subtitle = 'Search patient accounts and review platform activity';
    $tabs = [
        'all' => ['label' => 'All patients', 'count' => $activeCount + $bannedCount],
        'active' => ['label' => 'Active', 'count' => $activeCount],
        'banned' => ['label' => 'Suspended', 'count' => $bannedCount],
    ];
@endphp

@section('content')
    <div class="rounded-3xl bg-card p-5 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Patient account status">
                @foreach ($tabs as $key => $tab)
                    <a href="{{ route('admin.patients.index', ['status' => $key, 'search' => $search ?: null]) }}" @class([
                        'inline-flex shrink-0 items-center gap-2 rounded-full px-4 py-2 text-[12px] font-semibold transition',
                        'bg-ink text-white' => $status === $key,
                        'bg-secondary text-ink-soft hover:text-ink' => $status !== $key,
                    ])>{{ $tab['label'] }} <span @class(['rounded-full px-2 py-0.5 text-[10px]', 'bg-white/20' => $status === $key, 'bg-white' => $status !== $key])>{{ $tab['count'] }}</span></a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('admin.patients.index') }}" class="flex w-full gap-2 lg:max-w-md">
                <input type="hidden" name="status" value="{{ $status }}">
                <label class="relative flex-1"><span class="sr-only">Search patients</span><svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg><input name="search" value="{{ $search }}" placeholder="Name, email or mobile" class="w-full rounded-2xl border border-border bg-white py-2.5 pl-10 pr-4 text-[12px] outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10"></label>
                <button class="rounded-2xl bg-ink px-4 py-2.5 text-[12px] font-semibold text-white">Search</button>
            </form>
        </div>
    </div>

    <div class="mt-5 grid gap-4 xl:grid-cols-2">
        @forelse ($patients as $patient)
            <article class="rounded-3xl bg-card p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-start gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-ink/10 font-display text-sm font-semibold text-ink">{{ collect(preg_split('/\s+/', trim($patient->name)))->filter()->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('') }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2"><div class="min-w-0"><h2 class="truncate font-display text-[16px] font-medium text-ink">{{ $patient->name }}</h2><p class="mt-1 text-[11px] text-ink-soft">Patient ID #{{ str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT) }} · Joined {{ $patient->created_at->format('M j, Y') }}</p></div><x-dashboard.badge :status="$patient->is_banned ? 'banned' : 'active'" /></div>
                        <div class="mt-4 grid gap-2 text-[12px] text-ink-soft sm:grid-cols-2"><p class="truncate">{{ $patient->email }}</p><p class="truncate sm:text-right">{{ $patient->mobile }}</p></div>
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4">
                            <div class="flex gap-4 text-[11px] text-ink-soft"><span><strong class="text-ink">{{ $patient->appointments_count }}</strong> appointments</span><span><strong class="text-ink">{{ $patient->mood_entries_count }}</strong> mood entries</span></div>
                            <a href="{{ route('admin.patients.show', $patient) }}" class="rounded-xl bg-ink px-3 py-2 text-[11px] font-semibold text-white">View details</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-card px-6 py-14 text-center xl:col-span-2"><div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-secondary text-ink-soft"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a7 7 0 0 0-14 0v2"/><circle cx="12" cy="7" r="4"/></svg></div><h2 class="mt-4 font-display text-[16px] font-medium text-ink">No patients found</h2><p class="mt-1 text-[12px] text-ink-soft">Try another status or search term.</p></div>
        @endforelse
    </div>
    <div class="mt-5">{{ $patients->links() }}</div>
@endsection
