@php
    $notifications = auth('web')->user()->unreadNotifications()->latest()->take(10)->get();
@endphp

<details class="group relative">
    <summary class="relative grid h-11 w-11 shrink-0 list-none place-items-center rounded-full bg-card text-ink transition-transform marker:content-none hover:-translate-y-0.5" aria-label="Notifications">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        @if ($notifications->isNotEmpty())
            <span class="absolute top-1.5 right-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-red-600 px-1 text-[9px] font-semibold text-white">{{ $notifications->count() }}</span>
        @endif
    </summary>

    <div class="absolute right-0 z-20 mt-2 w-80 rounded-2xl bg-card p-2 shadow-lg">
        <p class="px-3 py-2 text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Notifications</p>
        <ul class="max-h-80 divide-y divide-border overflow-y-auto">
            @forelse ($notifications as $notification)
                <li>
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        <button type="submit" class="block w-full rounded-xl px-3 py-2.5 text-left hover:bg-secondary">
                            <p class="text-[12px] font-medium text-ink">{{ $notification->data['title'] ?? 'Notification' }}</p>
                            @if (isset($notification->data['anonymous_label']))
                                <p class="mt-0.5 text-[11px] text-ink-soft">You are {{ $notification->data['anonymous_label'] }}</p>
                            @endif
                            @if (isset($notification->data['scheduled_at']))
                                <p class="mt-0.5 text-[11px] text-ink-soft">{{ \Illuminate\Support\Carbon::parse($notification->data['scheduled_at'])->format('D, j M · g:i A') }}</p>
                            @endif
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-3 py-4 text-[12px] text-ink-soft">You're all caught up.</li>
            @endforelse
        </ul>
    </div>
</details>
