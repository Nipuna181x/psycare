<footer id="contact" class="px-3 pb-3 md:px-5 md:pb-5">
    <div class="rounded-3xl bg-ink px-6 py-14 text-primary-foreground md:px-12 md:py-20">
        <div class="grid gap-12 lg:grid-cols-[1.2fr_1.8fr]">
            <div>
                <p class="font-display text-2xl font-medium tracking-tight">PsyCare</p>
                <p class="mt-4 max-w-[34ch] text-[15px] leading-relaxed text-primary-foreground/65">One place to find and book every registered mental health professional in Sri Lanka.</p>
                <a href="{{ route('doctors.index') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-card px-6 py-3.5 text-[11px] font-semibold tracking-[0.12em] text-ink uppercase transition-transform hover:-translate-y-0.5">Book a doctor <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
            </div>
            <div class="grid gap-10 sm:grid-cols-3">
                @foreach ([['Care', ['Individual therapy', 'Psychiatry & medication', 'Group circles', 'Child & teen care']], ['Company', ['About PsyCare', 'For clinicians', 'Careers', 'Press']], ['Trust', ['Privacy & data', 'Confidentiality policy', 'care@psycare.lk', '+94 11 244 0000']]] as [$title, $links])
                    <div>
                        <p class="text-[11px] tracking-[0.16em] text-primary-foreground/45 uppercase">{{ $title }}</p>
                        <ul class="mt-5 space-y-2.5 text-[14px] text-primary-foreground/75">
                            @foreach ($links as $link)
                                <li class="transition-colors hover:text-primary-foreground">{{ $link }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-16 flex flex-wrap items-center justify-between gap-4 border-t border-primary-foreground/15 pt-6 text-[11px] tracking-[0.14em] text-primary-foreground/45 uppercase">
            <span>© {{ now()->year }} PsyCare Lanka (Pvt) Ltd</span>
            <span>In crisis? Call 1926 — free, 24 hours</span>
        </div>
    </div>
</footer>
