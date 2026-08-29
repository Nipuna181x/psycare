@props([
    'accent' => 'admin',
    'brand' => 'PsyCare',
    'roleLabel' => '',
    'links' => [],
    'logoutAction',
    'promoTitle' => null,
    'promoDescription' => null,
    'promoCtaLabel' => null,
    'promoCtaHref' => null,
    'showLogout' => true,
])

@php
    $theme = match ($accent) {
        'clinic' => ['solid' => 'bg-blue-900', 'text' => 'text-white', 'softText' => 'text-blue-800'],
        'doctor' => ['solid' => 'bg-sky-500', 'text' => 'text-white', 'softText' => 'text-sky-700'],
        default => ['solid' => 'bg-ink', 'text' => 'text-primary-foreground', 'softText' => 'text-ink'],
    };
@endphp

<aside {{ $attributes->class(['flex w-64 shrink-0 flex-col rounded-3xl bg-card p-5 lg:sticky lg:top-5 lg:h-[calc(100dvh-2.5rem)] lg:overflow-y-auto']) }}>
    <div class="flex items-center gap-2.5 px-1">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full {{ $theme['solid'] }} {{ $theme['text'] }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
        </span>
        <span class="font-display text-lg font-medium tracking-tight text-ink">{{ $brand }}</span>
    </div>

    @if ($roleLabel)
        <p class="mt-2 px-1 text-[10px] font-medium tracking-[0.14em] text-ink-soft uppercase">{{ $roleLabel }}</p>
    @endif

    <nav class="mt-8 flex-1 space-y-1">
        @foreach ($links as $link)
            <a
                href="{{ $link['href'] }}"
                class="flex items-center gap-3 rounded-2xl px-3.5 py-2.5 text-[13px] font-medium transition-colors {{ ($link['active'] ?? false) ? $theme['solid'].' '.$theme['text'] : 'text-ink-soft hover:bg-secondary hover:text-ink' }}"
            >
                <span class="grid h-5 w-5 shrink-0 place-items-center">{!! $link['icon'] !!}</span>
                <span class="min-w-0 flex-1">{{ $link['label'] }}</span>
                @if (($link['badge'] ?? 0) > 0)
                    <span class="grid h-5 min-w-5 place-items-center rounded-full px-1.5 text-[9px] font-bold {{ ($link['badgeTone'] ?? 'neutral') === 'danger' ? 'bg-red-600 text-white' : 'bg-secondary text-ink-soft' }}">{{ $link['badge'] > 99 ? '99+' : $link['badge'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if ($promoTitle)
        <div class="mt-6 rounded-2xl {{ $theme['solid'] }} {{ $theme['text'] }} p-5">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-white/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            </span>
            <p class="mt-3 font-display text-[14px] font-medium">{{ $promoTitle }}</p>
            <p class="mt-1 text-[12px] leading-relaxed opacity-80">{{ $promoDescription }}</p>
            @if ($promoCtaLabel)
                <a href="{{ $promoCtaHref }}" class="mt-4 block rounded-full bg-white py-2.5 text-center text-[11px] font-semibold tracking-[0.1em] uppercase {{ $theme['softText'] }} transition-transform hover:-translate-y-0.5">{{ $promoCtaLabel }}</a>
            @endif
        </div>
    @endif

    @if ($showLogout)
        <form method="POST" action="{{ $logoutAction }}" class="mt-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-2xl px-3.5 py-2.5 text-[13px] font-medium text-ink-soft transition-colors hover:bg-secondary hover:text-ink">
                <span class="grid h-5 w-5 shrink-0 place-items-center">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                </span>
                Sign out
            </button>
        </form>
    @endif
</aside>
