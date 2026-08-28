@extends('layouts.doctor')

@php
    $title = 'Clinic Requests';
    $subtitle = 'Work requests from clinics that want you on their team';
@endphp

@section('content')
    <div class="grid gap-5">
        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <div class="flex items-center justify-between gap-4 border-b border-border pb-4">
                <div>
                    <h2 class="font-display text-[16px] font-medium text-ink">Pending requests</h2>
                    <p class="mt-0.5 text-[12px] text-ink-soft">Accept to start seeing patients through this clinic, or decline.</p>
                </div>
                <span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft">{{ $pending->count() }}</span>
            </div>

            <ul class="mt-4 grid gap-2.5">
                @forelse ($pending as $affiliation)
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-border bg-white px-4 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $affiliation->clinic->name }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $affiliation->clinic->address }} · Requested {{ $affiliation->requested_by_clinic_at?->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('doctor.clinic-requests.decline', $affiliation) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="rounded-xl border border-border px-4 py-2 text-[11px] font-semibold tracking-[0.06em] text-ink-soft uppercase hover:bg-secondary">Decline</button>
                            </form>
                            <form method="POST" action="{{ route('doctor.clinic-requests.accept', $affiliation) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="rounded-xl bg-sky-700 px-4 py-2 text-[11px] font-semibold tracking-[0.06em] text-white uppercase hover:bg-sky-800">Accept</button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No pending requests right now.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Active affiliations</h2>
            <ul class="mt-4 divide-y divide-border">
                @forelse ($active as $affiliation)
                    <li class="flex items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $affiliation->clinic->name }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $affiliation->clinic->address }}</p>
                        </div>
                        <x-dashboard.badge :status="$affiliation->status" />
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">You're not affiliated with any clinic yet.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">History</h2>
            <ul class="mt-4 divide-y divide-border">
                @forelse ($history as $affiliation)
                    <li class="flex items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $affiliation->clinic->name }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $affiliation->clinic->address }}</p>
                        </div>
                        <x-dashboard.badge :status="$affiliation->status" />
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No past requests.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
