@props(['active' => null])

<nav class="mx-auto flex max-w-[1320px] items-center justify-between gap-4 px-5 py-6 md:px-9 md:py-7">
    <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink">
        <span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span>
        <span class="font-display text-lg font-medium tracking-tight">PsyCare</span>
    </a>

    <div class="hidden items-center gap-1 rounded-full bg-card px-2 py-1.5 shadow-[0_1px_0_0_var(--border)] lg:flex">
        @foreach ([['Home', '/'], ['Book a Doctor', route('doctors.index')], ['Lumi', '/ai-companion'], ['My Health Records', '/health-records'], ['My Appointments', route('appointments.index')], ['Group Therapy', route('therapy-rooms.index')], ['Mood Tracker', '/mood-tracker']] as [$label, $href])
            <a href="{{ $href }}" class="whitespace-nowrap rounded-full px-3 py-2 text-[12px] text-ink-soft transition-colors hover:bg-secondary hover:text-ink {{ request()->is(ltrim($href, '/')) ? 'bg-secondary text-ink' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @auth('web')
        <div class="flex items-center gap-2">
            <x-notification-bell />
            <a href="{{ route('appointments.index') }}" class="hidden rounded-full bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5 sm:inline-flex">My appointments</a>
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-ink text-[12px] font-semibold text-primary-foreground" title="{{ auth('web')->user()->name }}">{{ mb_strtoupper(mb_substr(auth('web')->user()->name, 0, 1)) }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" aria-label="Sign out" class="grid h-11 w-11 place-items-center rounded-full bg-card text-ink transition-transform hover:-translate-y-0.5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                </button>
            </form>
        </div>
    @else
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Log in <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
    @endauth
</nav>
