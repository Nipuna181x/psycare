@props(['items', 'accent' => 'admin'])

@php
    $max = max(1, collect($items)->max('value') ?? 1);

    $solid = match ($accent) {
        'clinic' => 'bg-blue-800',
        'doctor' => 'bg-sky-500',
        default => 'bg-ink',
    };
@endphp

<div class="flex h-44 items-end gap-4">
    @foreach ($items as $item)
        <div class="flex flex-1 flex-col items-center gap-2">
            <span class="text-[11px] font-medium text-ink">{{ $item['value'] }}</span>
            <div class="flex h-28 w-full items-end overflow-hidden rounded-xl bg-secondary">
                <div class="w-full rounded-t-xl {{ ($item['tone'] ?? 'accent') === 'muted' ? 'bg-ink-soft/25' : $solid }}" style="height: {{ max(6, (int) round($item['value'] / $max * 100)) }}%"></div>
            </div>
            <span class="text-center text-[11px] text-ink-soft">{{ $item['label'] }}</span>
        </div>
    @endforeach
</div>
