@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'rounded-3xl bg-card p-6']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="font-display text-[15px] font-medium text-ink">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-0.5 text-[12px] text-ink-soft">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($action)
            <div>{{ $action }}</div>
        @endisset
    </div>
    <div class="mt-5">
        {{ $slot }}
    </div>
</div>
