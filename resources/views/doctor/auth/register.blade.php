<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Doctor Registration — PsyCare Sri Lanka</title>
    <meta name="description" content="Register as a doctor on PsyCare's clinical portal.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="min-h-screen bg-background p-3 text-ink selection:bg-sky-500/15 md:p-6 lg:flex lg:items-center">
        <div class="mx-auto grid w-full max-w-[1320px] gap-5 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="relative hidden min-h-[760px] overflow-hidden rounded-3xl bg-ink lg:block">
                <img src="{{ Vite::asset('resources/images/psycare/hero-consult.jpg') }}" alt="A doctor speaking with a patient during a consultation" width="1200" height="900" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-ink/95 via-ink/25 to-ink/20"></div>

                <a href="{{ route('home') }}" class="absolute top-8 left-8 flex items-center gap-2.5 text-primary-foreground">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-primary-foreground/20 backdrop-blur-md"><span class="h-2.5 w-2.5 rounded-full bg-primary-foreground"></span></span>
                    <span class="font-display text-lg font-medium tracking-tight">PsyCare</span>
                </a>

                <div class="absolute inset-x-0 bottom-0 p-8 md:p-10">
                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-foreground/14 px-4 py-2 text-[11px] font-medium tracking-[0.08em] text-primary-foreground/80 uppercase backdrop-blur-md">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                        Doctor portal
                    </span>
                    <h2 class="display-head mt-5 max-w-[16ch] text-[clamp(2rem,4vw,3.4rem)] text-primary-foreground">Bring your practice onto PsyCare</h2>
                    <p class="mt-5 max-w-[48ch] text-[14px] leading-relaxed text-primary-foreground/72">Register once, then connect with clinics who want you on their team — no clinic sign-up required to get started.</p>
                </div>
            </section>

            <section class="flex min-h-[calc(100vh-1.5rem)] items-center rounded-3xl bg-card px-6 py-10 md:min-h-[calc(100vh-3rem)] md:px-10 lg:min-h-[760px] lg:px-14 xl:px-16">
                <div class="mx-auto w-full max-w-[600px]">
                    <div class="flex items-center justify-between gap-4 lg:hidden">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span>
                            <span class="font-display text-lg font-medium">PsyCare</span>
                        </a>
                        <span class="text-[10px] font-medium tracking-[0.12em] text-ink-soft uppercase">Doctor portal</span>
                    </div>

                    <span class="mt-10 inline-flex items-center gap-2 rounded-full bg-secondary px-3.5 py-1.5 text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase lg:mt-0">Step 1 of 2</span>
                    <h1 class="display-head mt-4 max-w-[17ch] text-[clamp(2rem,4.2vw,3.2rem)] text-ink">Register as a doctor</h1>
                    <p class="mt-4 max-w-[52ch] text-[14px] leading-relaxed text-ink-soft">Tell us the basics. You'll finish your professional profile next, then a PsyCare admin will review and approve your account.</p>

                    <form method="POST" action="{{ route('doctor.register') }}" class="mt-8 space-y-4">
                        @csrf

                        <label for="name" class="block">
                            <span class="text-[12px] text-ink-soft">Full name</span>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Dr. Amaya Silva" required autofocus autocomplete="name" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring @error('name') ring-2 ring-red-300 @enderror">
                            @error('name')
                                <span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label for="email" class="block">
                            <span class="text-[12px] text-ink-soft">Email</span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@email.com" required autocomplete="email" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring @error('email') ring-2 ring-red-300 @enderror">
                            @error('email')
                                <span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label for="license_number" class="block">
                                <span class="text-[12px] text-ink-soft">Medical licence no.</span>
                                <input id="license_number" name="license_number" type="text" value="{{ old('license_number') }}" placeholder="SLMC-12345" required class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring @error('license_number') ring-2 ring-red-300 @enderror">
                                @error('license_number')
                                    <span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>
                                @enderror
                            </label>

                            <label for="phone" class="block">
                                <span class="text-[12px] text-ink-soft">Phone</span>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+94 77 123 4567" autocomplete="tel" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring @error('phone') ring-2 ring-red-300 @enderror">
                                @error('phone')
                                    <span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>

                        <label for="password" class="block">
                            <span class="text-[12px] text-ink-soft">Password</span>
                            <input id="password" name="password" type="password" placeholder="••••••••" required autocomplete="new-password" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring @error('password') ring-2 ring-red-300 @enderror">
                            @error('password')
                                <span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label for="password_confirmation" class="block">
                            <span class="text-[12px] text-ink-soft">Confirm password</span>
                            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••" required autocomplete="new-password" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none transition-shadow focus:ring-2 focus:ring-ring">
                        </label>

                        <button type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-deep">Continue to step 2</button>
                    </form>

                    <p class="mt-6 text-[11px] leading-relaxed text-muted-foreground">Already registered? <a href="{{ route('doctor.login') }}" class="text-ink underline-offset-4 hover:underline">Sign in</a>.</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
