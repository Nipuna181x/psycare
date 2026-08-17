@props([
    'label',
    'value',
    'delta' => null,
    'deltaTone' => 'positive',
    'chip' => 'rose',
    'accent' => 'admin',
])

@php
    $chipMap = [
        'rose' => 'bg-rose-100 text-rose-600',
        'amber' => 'bg-amber-100 text-amber-600',
        'emerald' => 'bg-emerald-100 text-emerald-600',
        'accent' => match ($accent) {
            'clinic' => 'bg-purple-600 text-white',
            'doctor' => 'bg-sky-500 text-white',
            default => 'bg-ink text-primary-foreground',
        },
    ];

    $deltaClass = match ($deltaTone) {
        'negative' => 'text-red-600',
        'neutral' => 'text-ink-soft',
        default => 'text-emerald-600',
    };
@endphp

<div class="rounded-3xl bg-card p-5">
    <span class="grid h-10 w-10 place-items-center rounded-2xl {{ $chipMap[$chip] ?? $chipMap['rose'] }}">
        {{ $slot }}
    </span>
    <p class="mt-4 truncate font-display text-[22px] font-medium text-ink" title="{{ $value }}">{{ $value }}</p>
    <p class="mt-0.5 text-[12px] text-ink-soft">{{ $label }}</p>
    @if ($delta)
        <p class="mt-2 text-[11px] font-medium {{ $deltaClass }}">{{ $delta }}</p>
    @endif
</div>
