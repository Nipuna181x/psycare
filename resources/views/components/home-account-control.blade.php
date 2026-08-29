@auth('web')
    <span class="hidden rounded-full bg-card px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase sm:inline-flex">{{ Str::before(auth('web')->user()->name, ' ') }}</span>

    <details class="group relative">
        <summary class="grid h-11 w-11 shrink-0 cursor-pointer list-none place-items-center rounded-full bg-card text-ink marker:content-none" aria-label="Open account menu" title="{{ auth('web')->user()->name }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
        </summary>

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
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </details>
@else
    <span class="hidden rounded-full bg-card px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase sm:inline-flex">Sign in or sign up</span>
    <a href="{{ route('register') }}" aria-label="Create an account" class="grid h-11 w-11 place-items-center rounded-full bg-card text-ink transition-transform hover:-translate-y-0.5">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
    </a>
@endauth
