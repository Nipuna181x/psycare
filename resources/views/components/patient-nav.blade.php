@props(['active' => null])

<nav class="mx-auto flex max-w-[1320px] items-center justify-between gap-4 px-5 py-6 md:px-9 md:py-7">
    <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink">
        <span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span>
        <span class="font-display text-lg font-medium tracking-tight">PsyCare</span>
    </a>

    <div class="hidden items-center gap-1 rounded-full bg-card px-2 py-1.5 shadow-[0_1px_0_0_var(--border)] lg:flex">
        @foreach ([['Home', '/'], ['Book a Doctor', route('doctors.index')], ['Lumi', '/ai-companion'], ['My Health Records', route('health-records.index')], ['My Appointments', route('appointments.index')], ['Group Therapy', route('therapy-rooms.index')], ['Mood Tracker', route('mood-tracker.index')]] as [$label, $href])
            <a href="{{ $href }}" class="whitespace-nowrap rounded-full px-3 py-2 text-[12px] text-ink-soft transition-colors hover:bg-secondary hover:text-ink {{ request()->is(ltrim($href, '/')) ? 'bg-secondary text-ink' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @auth('web')
        <div class="flex items-center gap-2">
            <x-notification-bell />
            <a href="{{ route('appointments.index') }}" class="hidden rounded-full bg-secondary px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5 sm:inline-flex">My appointments</a>

            <details class="group relative">
                <summary class="grid h-11 w-11 shrink-0 cursor-pointer list-none place-items-center rounded-full bg-ink text-[12px] font-semibold text-primary-foreground marker:content-none" title="{{ auth('web')->user()->name }}">{{ mb_strtoupper(mb_substr(auth('web')->user()->name, 0, 1)) }}</summary>

                <div class="absolute right-0 z-30 mt-2 w-56 rounded-2xl border border-border bg-card p-2 shadow-xl">
                    <div class="px-3 py-2.5">
                        <p class="truncate text-[12px] font-medium text-ink">{{ auth('web')->user()->name }}</p>
                        <p class="truncate text-[11px] text-ink-soft">{{ auth('web')->user()->email }}</p>
                    </div>
                    <div class="border-t border-border pt-1">
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[12px] font-medium text-ink hover:bg-secondary">
                            <svg class="h-4 w-4 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z"/><circle cx="12" cy="12" r="3"/></svg>
                            Settings
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-border pt-1">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[12px] font-medium text-ink-soft hover:bg-secondary hover:text-ink">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        </div>
    @else
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Log in <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
    @endauth
</nav>
