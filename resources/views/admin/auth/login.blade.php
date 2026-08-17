<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Access — PsyCare Sri Lanka</title>
    <meta name="description" content="Secure administrator access to the PsyCare platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="min-h-screen bg-background p-3 text-ink selection:bg-teal/20 md:p-6 lg:flex lg:items-center">
        <div class="mx-auto grid w-full max-w-[1320px] gap-5 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="relative hidden overflow-hidden rounded-3xl bg-ink lg:block">
                <img src="{{ Vite::asset('resources/images/psycare/about-care.jpg') }}" alt="PsyCare clinical care team" width="1200" height="900" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink/95 via-ink/20 to-ink/25"></div>

                <a href="{{ route('home') }}" class="absolute top-8 left-8 flex items-center gap-2.5 text-primary-foreground">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-primary-foreground/20 backdrop-blur-md"><span class="h-2.5 w-2.5 rounded-full bg-primary-foreground"></span></span>
                    <span class="font-display text-lg font-medium tracking-tight">PsyCare</span>
                </a>

                <div class="absolute inset-x-0 bottom-0 p-8 md:p-10">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-foreground/14 px-4 py-2 text-[11px] font-medium tracking-[0.08em] text-primary-foreground/80 uppercase backdrop-blur-md">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                        Platform administration
                    </span>
                    <h2 class="display-head mt-5 max-w-[17ch] text-[clamp(2rem,4vw,3.4rem)] text-primary-foreground">One trusted console for every standard of care</h2>
                    <p class="mt-5 max-w-[48ch] text-[14px] leading-relaxed text-primary-foreground/72">Review medical centres, manage platform access, and protect the quality and privacy standards behind every PsyCare experience.</p>
                </div>
            </section>

            <section class="flex items-center rounded-3xl bg-card px-6 py-10 md:px-10 lg:px-14 xl:px-16">
                <div class="mx-auto w-full max-w-[600px]">
                    <div class="flex items-center justify-between gap-4 lg:hidden">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink"><span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span><span class="font-display text-lg font-medium">PsyCare</span></a>
                        <span class="text-[10px] font-medium tracking-[0.12em] text-ink-soft uppercase">Admin portal</span>
                    </div>

                    <p class="eyebrow mt-10 lg:mt-0">Secure account access</p>
                    <h1 class="display-head mt-3 max-w-[18ch] text-[clamp(2rem,4.2vw,3.2rem)] text-ink">Welcome back, administrator</h1>
                    <p class="mt-4 max-w-[52ch] text-[14px] leading-relaxed text-ink-soft">Use your authorised PsyCare credentials to continue to the administration console.</p>

                    <div class="mt-8 grid gap-2 sm:grid-cols-2">
                        <div class="rounded-2xl bg-ink p-4 text-primary-foreground">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                            <span class="mt-3 block font-display text-[15px] font-medium">Platform administrator</span>
                            <span class="mt-0.5 block text-[12px] text-primary-foreground/65">Operational oversight</span>
                        </div>
                        <div class="rounded-2xl bg-secondary p-4 text-ink-soft">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span class="mt-3 block font-display text-[15px] font-medium text-ink">Protected session</span>
                            <span class="mt-0.5 block text-[12px]">Encrypted and monitored</span>
                        </div>
                    </div>

                    <div class="mt-6 inline-flex rounded-full bg-secondary p-1">
                        <span class="rounded-full bg-card px-5 py-2 text-[12px] font-medium text-ink shadow-[0_1px_0_0_var(--border)]">Administrator sign in</span>
                    </div>

                    @if (session('status'))
                        <p class="mt-5 rounded-2xl bg-secondary px-4 py-3 text-[12px] text-ink-soft">{{ session('status') }}</p>
                    @endif

                    <form method="POST" action="{{ route('admin.login') }}" class="mt-7 space-y-4">
                        @csrf
                        <label for="email" class="block">
                            <span class="text-[12px] text-ink-soft">Email</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@psycare.lk" required autofocus autocomplete="username" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring">
                            @error('email')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label for="password" class="block">
                            <span class="text-[12px] text-ink-soft">Password</span>
                            <input id="password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring">
                            @error('password')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <div class="flex items-center justify-between gap-4 text-[12px] text-ink-soft">
                            <label class="flex items-center gap-2"><input type="checkbox" name="remember" class="accent-teal-deep"> Keep me signed in</label>
                            <span class="text-muted-foreground">Authorised access only</span>
                        </div>
                        <button type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Continue to admin console</button>
                    </form>

                    <p class="mt-6 text-[11px] leading-relaxed text-muted-foreground">All administrator activity may be recorded for security and compliance purposes.</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
