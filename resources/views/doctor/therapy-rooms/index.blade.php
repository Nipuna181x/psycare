@extends('layouts.doctor')

@php
    $title = 'Group Sessions';
    $subtitle = 'Anonymous peer support rooms you host';
@endphp

@section('content')
    <div class="grid gap-5">
        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-semibold tracking-[0.12em] text-sky-700 uppercase">Facilitator workspace</p>
                        <h2 class="mt-1 font-display text-[17px] font-medium text-ink">Plan and host safe group care</h2>
                        <p class="mt-1 max-w-[65ch] text-[12px] leading-relaxed text-ink-soft">Schedule moderated sessions, review anonymous attendance, and enter live rooms from one private workspace.</p>
                    </div>
                </div>
                <a href="{{ route('doctor.therapy-rooms.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-sky-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-sky-500">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Schedule a room
                </a>
            </div>
        </section>

        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="upcoming-sessions-heading">
            <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                <div>
                    <h2 id="upcoming-sessions-heading" class="font-display text-[16px] font-medium text-ink">Upcoming</h2>
                    <p class="mt-0.5 text-[12px] text-ink-soft">{{ $upcoming->count() }} room{{ $upcoming->count() === 1 ? '' : 's' }}</p>
                </div>
                <span class="rounded-full bg-sky-50 px-3 py-1 text-[10px] font-semibold text-sky-700">{{ $upcoming->count() }}</span>
            </div>

            <ul class="mt-4 grid gap-2.5">
                @forelse ($upcoming as $room)
                    @include('doctor.therapy-rooms._row', ['room' => $room])
                @empty
                    <li class="grid min-h-44 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-6 py-8 text-center">
                        <div>
                            <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-white text-ink-soft shadow-[0_1px_0_0_var(--border)]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M12 14v4M10 16h4"/></svg>
                            </span>
                            <p class="mt-3 text-[12px] font-medium text-ink">No group sessions scheduled yet.</p>
                            <p class="mt-1 text-[11px] text-ink-soft">Create a room when you are ready to plan the next facilitated circle.</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6" aria-labelledby="session-history-heading">
            <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                <div>
                    <h2 id="session-history-heading" class="font-display text-[16px] font-medium text-ink">History</h2>
                    <p class="mt-0.5 text-[12px] text-ink-soft">Completed & cancelled</p>
                </div>
                <span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft">{{ $history->count() }}</span>
            </div>

            <ul class="mt-4 grid gap-2.5">
                @forelse ($history as $room)
                    @include('doctor.therapy-rooms._row', ['room' => $room])
                @empty
                    <li class="grid min-h-36 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 px-6 py-8 text-center">
                        <div>
                            <svg class="mx-auto h-5 w-5 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                            <p class="mt-2 text-[12px] font-medium text-ink">No past group sessions yet.</p>
                            <p class="mt-1 text-[11px] text-ink-soft">Completed and cancelled rooms will remain available here.</p>
                        </div>
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
