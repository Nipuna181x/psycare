<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lumi — PsyCare AI Companion</title>
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/ai-companion.js'])
</head>
<body class="overflow-hidden bg-black">
    <main id="voice-companion" data-start-endpoint="{{ route('ai-companion.start') }}" data-endpoint="{{ route('ai-companion.respond') }}" data-finish-endpoint="{{ route('ai-companion.finish') }}" data-home="{{ route('home') }}" class="relative flex min-h-dvh flex-col overflow-hidden bg-[radial-gradient(circle_at_50%_45%,#211c28_0%,#0d0b10_38%,#050505_72%)] text-white">
        <header class="relative z-20 flex items-center justify-between gap-4 px-5 py-5 md:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-full border border-white/10 bg-white/6 py-2 pr-4 pl-2.5 text-white/80 transition hover:bg-white/10 hover:text-white">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                <span class="text-[12px] font-medium tracking-tight">Back to site</span>
            </a>
            <div class="flex rounded-full border border-white/10 bg-white/6 p-1" role="radiogroup" aria-label="Conversation language">
                <button type="button" data-language="en" role="radio" aria-checked="true" class="companion-language rounded-full bg-white px-3 py-1.5 text-[11px] font-semibold text-black">EN</button>
                <button type="button" data-language="si" role="radio" aria-checked="false" class="companion-language rounded-full px-3 py-1.5 text-[11px] font-semibold text-white/55" lang="si">සිං</button>
            </div>
        </header>

        <section class="relative z-10 flex flex-1 flex-col items-center justify-center px-5 pb-28">
            <div class="relative grid size-64 place-items-center sm:size-72 md:size-80" aria-hidden="true">
                <span id="orb-halo" class="absolute inset-6 rounded-full bg-fuchsia-400/15 blur-3xl transition-all duration-700"></span>
                <span id="voice-orb" class="companion-orb relative block size-44 rounded-full sm:size-52 md:size-56">
                    <span class="companion-ribbon companion-ribbon-a"></span>
                    <span class="companion-ribbon companion-ribbon-b"></span>
                    <span class="companion-ribbon companion-ribbon-c"></span>
                    <span class="companion-core"></span>
                </span>
            </div>
            <p id="companion-greeting" class="mt-6 max-w-sm text-center text-lg font-medium tracking-wide text-white/90">Hi, I'm Lumi, a friend to express how you feel.</p>
            <p id="companion-status" class="mt-3 min-h-6 text-center text-sm tracking-wide text-white/60" aria-live="polite">Tap the microphone to begin</p>
            <p class="mt-2 max-w-md text-center text-xs leading-relaxed text-white/30">A supportive AI companion, not a replacement for professional care. In a crisis, call 1926 or emergency services.</p>
            <p class="mt-3 max-w-lg text-center text-[11px] leading-relaxed text-white/25">By starting, you consent to securely storing this conversation and combining it with your screening results to prepare an AI-generated report for clinician review.</p>
        </section>

        <div class="absolute inset-x-0 bottom-0 z-20 px-4 pb-[max(1rem,env(safe-area-inset-bottom))] md:px-8 md:pb-7">
            <div class="mx-auto flex max-w-xl items-center justify-between rounded-full border border-white/10 bg-[#242424]/95 p-2 shadow-2xl backdrop-blur-xl">
                <span class="pl-4 text-xs text-white/35">Talk with Lumi · voice only</span>
                <div class="flex items-center gap-2">
                    <button id="microphone-button" type="button" aria-label="Start listening" class="grid size-11 place-items-center rounded-full text-white/70 transition hover:bg-white/8 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-300">
                        <svg id="microphone-icon" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3M8 22h8"/></svg>
                    </button>
                    <button id="end-call-button" type="button" aria-label="End conversation" class="grid size-11 place-items-center rounded-full bg-white text-black transition hover:bg-white/85 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <audio id="companion-audio" hidden></audio>
    </main>
</body>
</html>
