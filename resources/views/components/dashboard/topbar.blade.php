@props([
    'title',
    'subtitle' => null,
    'userName',
    'roleLabel',
    'accent' => 'admin',
    'notifications' => collect(),
    'notificationCount' => 0,
    'notificationsRoute' => null,
    'notificationReadRouteName' => null,
    'profileHref' => null,
    'logoutAction' => null,
    'avatarUrl' => null,
    'contextSwitcher' => null,
])

@php
    $theme = match ($accent) {
        'clinic' => ['avatarBg' => 'bg-purple-600', 'avatarText' => 'text-white'],
        'doctor' => ['avatarBg' => 'bg-sky-500', 'avatarText' => 'text-white'],
        default => ['avatarBg' => 'bg-ink', 'avatarText' => 'text-primary-foreground'],
    };

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<header class="flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-card px-6 py-4">
    <div>
        <h1 class="font-display text-[20px] font-medium text-ink">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-0.5 text-[13px] text-ink-soft">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="flex items-center gap-3">
        {{ $contextSwitcher }}

        @if ($notificationsRoute)
            <x-dashboard.doctor-notifications :notifications="$notifications" :count="$notificationCount" :index-route="$notificationsRoute" :read-route-name="$notificationReadRouteName" />
        @else
            <span class="grid h-10 w-10 place-items-center rounded-full bg-secondary text-ink-soft">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
            </span>
        @endif

        @if ($profileHref)
            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-2.5 rounded-full bg-secondary py-1.5 pr-4 pl-1.5 marker:content-none">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="" class="h-8 w-8 rounded-full object-cover">
                    @else
                        <span class="grid h-8 w-8 place-items-center rounded-full {{ $theme['avatarBg'] }} text-[11px] font-semibold {{ $theme['avatarText'] }}">{{ $initials }}</span>
                    @endif
                    <span class="leading-tight">
                        <span class="block text-[12px] font-medium text-ink">{{ $userName }}</span>
                        <span class="block text-[10px] text-ink-soft">{{ $roleLabel }}</span>
                    </span>
                    <svg class="h-3.5 w-3.5 text-ink-soft transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <div class="absolute right-0 z-30 mt-2 w-56 rounded-2xl border border-border bg-card p-2 shadow-xl">
                    <a href="{{ $profileHref }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[12px] font-medium text-ink hover:bg-secondary">
                        <svg class="h-4 w-4 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        Profile & Settings
                    </a>
                    <form method="POST" action="{{ $logoutAction }}" class="mt-1 border-t border-border pt-1">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[12px] font-medium text-ink-soft hover:bg-secondary hover:text-ink">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </details>
        @else
            <div class="flex items-center gap-2.5 rounded-full bg-secondary py-1.5 pr-4 pl-1.5">
                <span class="grid h-8 w-8 place-items-center rounded-full {{ $theme['avatarBg'] }} text-[11px] font-semibold {{ $theme['avatarText'] }}">{{ $initials }}</span>
                <div class="leading-tight">
                    <p class="text-[12px] font-medium text-ink">{{ $userName }}</p>
                    <p class="text-[10px] text-ink-soft">{{ $roleLabel }}</p>
                </div>
            </div>
        @endif
    </div>
</header>
