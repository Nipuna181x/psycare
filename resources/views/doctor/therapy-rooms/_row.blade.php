<li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
    <a href="{{ route('doctor.therapy-rooms.show', $room) }}" class="flex min-w-0 items-center gap-3">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-sky-100 text-[12px] font-semibold text-sky-700">{{ mb_strtoupper(mb_substr($room->title, 0, 1)) }}</span>
        <div class="min-w-0">
            <p class="truncate text-[13px] font-medium text-ink">{{ $room->title }}</p>
            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $room->scheduled_at->format('D, j M · g:i A') }} · {{ $room->duration_minutes }} min · {{ $room->active_participants_count }} patient{{ $room->active_participants_count === 1 ? '' : 's' }}</p>
        </div>
    </a>
    <x-dashboard.badge :status="$room->status" />
</li>
