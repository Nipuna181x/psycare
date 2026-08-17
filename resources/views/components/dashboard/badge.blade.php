@props(['status'])

@php
    $tone = match (mb_strtolower($status)) {
        'approved', 'active' => 'bg-emerald-100 text-emerald-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'rejected', 'inactive' => 'bg-red-100 text-red-700',
        default => 'bg-secondary text-ink-soft',
    };
@endphp

<span class="inline-flex items-center rounded-full {{ $tone }} px-2.5 py-1 text-[10px] font-semibold tracking-[0.06em] uppercase">{{ $status }}</span>
