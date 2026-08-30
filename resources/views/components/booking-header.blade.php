@props(['doctor', 'step'])

@php
    $steps = ['Schedule', 'Details', 'Pre-assessment', 'Review'];
@endphp

<header class="mx-auto max-w-[840px] px-5 pt-8 md:px-9">
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink">
            <span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span>
            <span class="font-display text-lg font-semibold tracking-[0.08em] uppercase">PsyCare</span>
        </a>
        <a href="{{ route('doctors.show', $doctor) }}" class="text-[12px] text-ink-soft transition-colors hover:text-ink">Exit booking</a>
    </div>

    <ol class="mt-8 flex items-center gap-2">
        @foreach ($steps as $index => $label)
            @php $number = $index + 1; @endphp
            <li class="flex flex-1 items-center gap-2">
                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-[11px] font-semibold {{ $number < $step ? 'bg-teal-deep text-primary-foreground' : ($number === $step ? 'bg-ink text-primary-foreground' : 'bg-secondary text-ink-soft') }}">
                    @if ($number < $step)
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    @else
                        {{ $number }}
                    @endif
                </span>
                <span class="hidden text-[12px] font-medium {{ $number <= $step ? 'text-ink' : 'text-ink-soft' }} sm:inline">{{ $label }}</span>
                @if (! $loop->last)
                    <span class="h-px flex-1 {{ $number < $step ? 'bg-teal-deep' : 'bg-border' }}"></span>
                @endif
            </li>
        @endforeach
    </ol>
</header>
