@props(['notifications', 'count', 'indexRoute', 'readRouteName'])

<details class="group relative">
    <summary class="relative grid h-10 w-10 cursor-pointer list-none place-items-center rounded-full bg-secondary text-ink-soft marker:content-none hover:text-ink" aria-label="Notifications, {{ $count }} unread">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        @if ($count > 0)
            <span class="absolute -top-0.5 -right-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-red-600 px-1 text-[8px] font-bold text-white">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </summary>

    <div class="absolute right-0 z-30 mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-border bg-card shadow-xl">
        <div class="flex items-center justify-between border-b border-border px-4 py-3">
            <p class="font-display text-[13px] font-medium text-ink">Notifications</p>
            @if ($count > 0)<span class="text-[10px] font-medium text-red-600">{{ $count }} unread</span>@endif
        </div>
        <ul class="max-h-80 overflow-y-auto p-2">
            @forelse ($notifications as $notification)
                <li>
                    <form method="POST" action="{{ route($readRouteName, $notification->id) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl px-3 py-3 text-left hover:bg-secondary {{ $notification->read_at ? '' : 'bg-sky-50/70' }}">
                            <p class="text-[12px] leading-relaxed {{ $notification->read_at ? 'font-medium text-ink-soft' : 'font-semibold text-ink' }}">{{ $notification->data['message'] ?? 'Doctor portal update' }}</p>
                            <p class="mt-1 text-[10px] text-ink-soft">{{ $notification->created_at->diffForHumans() }}</p>
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-[12px] text-ink-soft">You're all caught up.</li>
            @endforelse
        </ul>
        <a href="{{ $indexRoute }}" class="block border-t border-border px-4 py-3 text-center text-[11px] font-semibold tracking-[0.08em] text-sky-700 uppercase hover:bg-sky-50">View all</a>
    </div>
</details>
