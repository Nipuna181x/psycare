@props([
    'title',
    'subtitle' => null,
    'userName',
    'roleLabel',
    'accent' => 'admin',
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
        <span class="grid h-10 w-10 place-items-center rounded-full bg-secondary text-ink-soft">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
        </span>
        <div class="flex items-center gap-2.5 rounded-full bg-secondary py-1.5 pr-4 pl-1.5">
            <span class="grid h-8 w-8 place-items-center rounded-full {{ $theme['avatarBg'] }} text-[11px] font-semibold {{ $theme['avatarText'] }}">{{ $initials }}</span>
            <div class="leading-tight">
                <p class="text-[12px] font-medium text-ink">{{ $userName }}</p>
                <p class="text-[10px] text-ink-soft">{{ $roleLabel }}</p>
            </div>
        </div>
    </div>
</header>
