@props([
    'chartData',
    'emptyMessage' => 'Log a few daily check-ins to see your mood trend here.',
])

@if (count($chartData['scores']) > 0)
    <div {{ $attributes->class(['h-64']) }}>
        <canvas data-mood-trend-chart data-chart="{{ json_encode($chartData) }}" aria-label="Mood scores over the last 30 days"></canvas>
    </div>
@else
    <div {{ $attributes->class(['grid min-h-48 place-items-center rounded-2xl border border-dashed border-border bg-secondary/40 p-6 text-center']) }}>
        <div>
            <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-amber-50 text-xl" aria-hidden="true">☀️</span>
            <p class="mt-3 text-[12px] text-ink-soft">{{ $emptyMessage }}</p>
        </div>
    </div>
@endif
