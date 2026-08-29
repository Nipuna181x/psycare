<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Sign In — PsyCare Clinic Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="min-h-screen bg-background p-3 text-ink selection:bg-blue-800/15 md:p-6 lg:flex lg:items-center">
        <div class="mx-auto w-full max-w-[520px] rounded-3xl bg-card px-6 py-10 md:px-10">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink"><span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span><span class="font-display text-lg font-medium">PsyCare</span></a>
                <span class="text-[10px] font-medium tracking-[0.12em] text-ink-soft uppercase">Clinic portal</span>
            </div>

            <p class="eyebrow mt-10">Staff account access</p>
            <h1 class="display-head mt-3 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">Staff sign in</h1>
            <p class="mt-4 text-[14px] leading-relaxed text-ink-soft">Sign in with the staff credentials your clinic administrator created for you.</p>

            @if (session('status'))
                <p class="mt-5 rounded-2xl bg-secondary px-4 py-3 text-[12px] text-ink-soft">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('medical-center.staff.login') }}" class="mt-7 space-y-4">
                @csrf
                <label for="email" class="block">
                    <span class="text-[12px] text-ink-soft">Email</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@clinic.lk" required autofocus autocomplete="username" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring">
                    @error('email')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                </label>
                <label for="password" class="block">
                    <span class="text-[12px] text-ink-soft">Password</span>
                    <input id="password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring">
                    @error('password')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="flex items-center gap-2 text-[12px] text-ink-soft"><input type="checkbox" name="remember" class="accent-blue-800"> Keep me signed in</label>
                <button type="submit" class="w-full rounded-full bg-blue-800 px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-white uppercase transition-transform hover:-translate-y-0.5 hover:bg-blue-900">Sign in</button>
            </form>

            <p class="mt-6 text-[12px] text-ink-soft">Signing in as the clinic administrator instead? <a href="{{ route('medical-center.login') }}" class="text-blue-800 underline-offset-4 hover:underline">Go to clinic login</a></p>
        </div>
    </main>
</body>
</html>
