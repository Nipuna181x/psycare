@props(['items', 'accent' => 'admin', 'emptyLabel' => 'No data yet.'])

@php
    $max = max(1, collect($items)->max('value') ?? 1);

    $barColor = match ($accent) {
        'clinic' => 'bg-purple-500',
        'doctor' => 'bg-sky-500',
        default => 'bg-ink',
    };
@endphp

<ul class="space-y-4">
    @forelse ($items as $item)
        <li>
            <div class="flex items-center justify-between text-[12px]">
                <span class="text-ink">{{ $item['label'] }}</span>
                <span class="text-ink-soft">{{ $item['display'] ?? $item['value'] }}</span>
            </div>
            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-secondary">
                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ max(4, (int) round($item['value'] / $max * 100)) }}%"></div>
            </div>
        </li>
    @empty
        <li class="text-[12px] text-ink-soft">{{ $emptyLabel }}</li>
    @endforelse
</ul>
