@php
    $statusLabel = match ($room->status) {
        'scheduled' => 'Upcoming',
        'live' => 'Live',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        default => ucfirst($room->status),
    };

    $statusTone = match ($room->status) {
        'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'live' => 'bg-red-50 text-red-700 ring-red-100',
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-200',
        default => 'bg-secondary text-ink-soft ring-border',
    };
@endphp

<li class="group rounded-2xl border border-border bg-white transition-[border-color,box-shadow,translate] duration-200 hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-[0_14px_30px_-24px_rgba(14,116,144,0.45)] focus-within:border-sky-300 focus-within:ring-2 focus-within:ring-sky-500/20">
    <a href="{{ route('doctor.therapy-rooms.show', $room) }}" class="flex flex-col gap-4 rounded-2xl p-4 outline-none sm:flex-row sm:items-center sm:justify-between" aria-label="View group session {{ $room->title }}">
        <div class="flex min-w-0 items-start gap-3.5 sm:items-center">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate text-[13px] font-semibold text-ink">{{ $room->title }}</p>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-semibold tracking-[0.06em] uppercase ring-1 ring-inset {{ $statusTone }}">
                        @if ($room->status === 'live')
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-500 opacity-60 motion-reduce:animate-none"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-red-600"></span>
                            </span>
                        @endif
                        {{ $statusLabel }}
                    </span>
                </div>
                @if ($room->topic)
                    <p class="mt-1 line-clamp-1 text-[11px] text-ink-soft">{{ $room->topic }}</p>
                @endif
                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-ink-soft">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/></svg>
                        {{ $room->scheduled_at->format('D, j M · g:i A') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        {{ $room->duration_minutes }} min
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                        {{ $room->active_participants_count }} participant{{ $room->active_participants_count === 1 ? '' : 's' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pl-14 sm:pl-0">
            <span class="text-[10px] font-medium text-ink-soft">View session</span>
            <svg class="h-4 w-4 text-ink-soft transition-[translate,color] group-hover:translate-x-0.5 group-hover:text-sky-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
        </div>
    </a>
</li>
