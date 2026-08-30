<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $therapyRoom->title }} — PsyCare</title>
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/therapy-room.js'])
</head>
<body class="overflow-hidden bg-black">
    <main
        id="therapy-room"
        data-room-id="{{ $therapyRoom->id }}"
        data-role="{{ $role }}"
        data-my-id="{{ $myId }}"
        data-my-label="{{ $myLabel }}"
        data-signal-endpoint="{{ $role === 'doctor' ? route('doctor.therapy-rooms.signal', $therapyRoom) : route('therapy-rooms.signal', $therapyRoom) }}"
        data-end-endpoint="{{ $role === 'doctor' ? route('doctor.therapy-rooms.end', $therapyRoom) : '' }}"
        data-roster-endpoint="{{ $role === 'doctor' ? route('doctor.therapy-rooms.roster', $therapyRoom) : '' }}"
        data-kick-endpoint-template="{{ $role === 'doctor' ? route('doctor.therapy-rooms.participants.kick', [$therapyRoom, 'PARTICIPANT_ID']) : '' }}"
        data-redirect="{{ $role === 'doctor' ? route('doctor.therapy-rooms.show', $therapyRoom) : route('therapy-rooms.show', $therapyRoom) }}"
        data-stun-servers="stun:stun.l.google.com:19302,stun:stun1.l.google.com:19302"
        data-turn-url="{{ config('services.turn.url') }}"
        data-turn-username="{{ config('services.turn.username') }}"
        data-turn-credential="{{ config('services.turn.credential') }}"
        class="relative flex min-h-dvh flex-col overflow-hidden bg-[radial-gradient(circle_at_50%_45%,#211c28_0%,#0d0b10_38%,#050505_72%)] text-white"
    >
        <header class="relative z-20 flex items-center justify-between gap-4 px-5 py-5 md:px-8">
            <div class="flex items-center gap-2.5 text-white/90">
                <span class="grid size-8 place-items-center rounded-full border border-white/15 bg-white/10"><span class="size-2.5 rounded-full bg-[#bac5ff]"></span></span>
                <span class="text-sm font-medium tracking-tight">{{ $therapyRoom->title }} <span class="font-normal text-white/35">· you are {{ $myLabel }}</span></span>
            </div>

            @if ($role === 'doctor')
                <button id="end-room-button" type="button" class="rounded-full bg-red-500/90 px-4 py-2 text-[11px] font-semibold tracking-[0.08em] text-white uppercase transition hover:bg-red-500">End session for everyone</button>
            @endif
        </header>

        <section id="video-grid" class="relative z-10 grid flex-1 auto-rows-fr grid-cols-1 gap-3 px-4 pb-28 sm:grid-cols-2 lg:grid-cols-3" aria-live="polite"></section>

        <template id="tile-template">
            <div class="tile relative overflow-hidden rounded-2xl bg-white/5" data-tile>
                <video class="h-full w-full object-cover" autoplay playsinline></video>
                <p class="absolute bottom-2 left-3 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-medium text-white" data-tile-label></p>
                <div class="absolute top-2 right-2 hidden gap-1" data-tile-doctor-controls>
                    <button type="button" class="rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-semibold text-white uppercase hover:bg-black/80" data-tile-mute>Mute</button>
                    <button type="button" class="rounded-full bg-red-500/80 px-2.5 py-1 text-[10px] font-semibold text-white uppercase hover:bg-red-500" data-tile-remove>Remove</button>
                </div>
            </div>
        </template>

        <div class="absolute inset-x-0 bottom-0 z-20 px-4 pb-[max(1rem,env(safe-area-inset-bottom))] md:px-8 md:pb-7">
            <div class="mx-auto flex max-w-xl items-center justify-between rounded-full border border-white/10 bg-[#242424]/95 p-2 shadow-2xl backdrop-blur-xl">
                <span id="call-status" class="pl-4 text-xs text-white/35">Connecting…</span>
                <div class="flex items-center gap-2">
                    <button id="toggle-mic-button" type="button" aria-label="Mute microphone" class="grid size-11 place-items-center rounded-full text-white/70 transition hover:bg-white/8 hover:text-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3M8 22h8"/></svg>
                    </button>
                    <button id="toggle-camera-button" type="button" aria-label="Turn off camera" class="grid size-11 place-items-center rounded-full text-white/70 transition hover:bg-white/8 hover:text-white">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2"/></svg>
                    </button>
                    <button id="leave-call-button" type="button" aria-label="Leave call" class="grid size-11 place-items-center rounded-full bg-white text-black transition hover:bg-white/85">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
