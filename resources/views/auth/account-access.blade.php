<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in or Register — PsyCare Sri Lanka</title>
    <meta name="description" content="Sign in to PsyCare as a patient to manage appointments, or as a clinic to publish your clinicians and availability.">
    <meta property="og:title" content="Log in or Register — PsyCare Sri Lanka">
    <meta property="og:description" content="Patient and clinic accounts for Sri Lanka's single mental health booking platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="account-access" class="min-h-screen bg-background text-ink selection:bg-teal/20" data-role="{{ $initialRole }}" data-mode="{{ $initialMode }}">
        <nav class="mx-auto flex max-w-[1320px] items-center justify-between gap-4 px-5 py-6 md:px-9 md:py-7">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink"><span class="grid h-8 w-8 place-items-center rounded-full bg-ink"><span class="h-2.5 w-2.5 rounded-full bg-card"></span></span><span class="font-display text-lg font-medium tracking-tight">PsyCare</span></a>
            <div class="hidden items-center gap-1 rounded-full bg-card px-2 py-1.5 shadow-[0_1px_0_0_var(--border)] lg:flex"><a href="{{ route('home') }}" class="rounded-full px-4 py-2 text-[13px] text-ink-soft transition-colors hover:text-ink">Home</a><a href="{{ route('doctors.index') }}" class="rounded-full px-4 py-2 text-[13px] text-ink-soft transition-colors hover:text-ink">Doctors</a></div>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Log in <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg></a>
        </nav>

        <main class="mx-auto max-w-[1320px] px-5 pb-16 md:px-9 md:pb-24">
            <div class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
                <section class="relative hidden overflow-hidden rounded-3xl bg-ink lg:block">
                    <img src="{{ Vite::asset('resources/images/psycare/about-care.jpg') }}" alt="A counsellor speaking with a patient in a calm consulting room" width="1200" height="1500" loading="lazy" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/25 to-ink/40"></div>
                    <div class="absolute inset-x-0 bottom-0 p-8"><h2 class="display-head max-w-[18ch] text-[clamp(1.6rem,2.6vw,2.4rem)] text-primary-foreground">One account, every clinic on the island</h2><p class="mt-4 max-w-[38ch] text-[14px] leading-relaxed text-primary-foreground/75">Patients keep their assessments, notes and upcoming sessions in one place. Clinics manage clinicians, rooms and live availability.</p></div>
                </section>

                <section class="rounded-3xl bg-card p-6 md:p-10">
                    <p class="eyebrow">Account access</p>
                    <h1 id="account-heading" class="display-head mt-3 text-[clamp(1.8rem,3.2vw,2.6rem)] text-ink"></h1>

                    @if (session('status'))
                        <p class="mt-5 rounded-2xl bg-secondary px-4 py-3 text-[12px] text-ink-soft">{{ session('status') }}</p>
                    @endif

                    <div class="mt-8 grid grid-cols-2 gap-2">
                        <button type="button" data-role-button="patient" class="rounded-2xl p-4 text-left"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span class="mt-3 block font-display text-[15px] font-medium">Patient</span><span class="mt-0.5 block text-[12px] opacity-70">Book & track care</span></button>
                        <button type="button" data-role-button="clinic" class="rounded-2xl p-4 text-left"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12h4M10 8h4M14 21v-3a2 2 0 0 0-4 0v3"/><path d="M6 10H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2h-2"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg><span class="mt-3 block font-display text-[15px] font-medium">Clinic</span><span class="mt-0.5 block text-[12px] opacity-70">Manage clinicians</span></button>
                    </div>

                    <div class="mt-6 inline-flex rounded-full bg-secondary p-1"><button type="button" data-mode-button="login" class="rounded-full px-5 py-2 text-[12px]">Log in</button><button type="button" data-mode-button="register" class="rounded-full px-5 py-2 text-[12px]">Register</button></div>

                    <form id="account-form" method="POST" class="mt-7 space-y-4">
                        @csrf
                        <label data-field="name" class="block"><span data-name-label class="text-[12px] text-ink-soft">Full name</span><input name="name" type="text" value="{{ old('name') }}" placeholder="Amaya Silva" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label data-field="registration_number" class="block"><span class="text-[12px] text-ink-soft">Ministry of Health registration no.</span><input name="registration_number" type="text" value="{{ old('registration_number') }}" placeholder="PHSRC/2019/0421" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label data-field="phone" class="block"><span class="text-[12px] text-ink-soft">Clinic phone</span><input name="phone" type="tel" value="{{ old('phone') }}" placeholder="+94 11 234 5678" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label data-field="address" class="block"><span class="text-[12px] text-ink-soft">Clinic address</span><input name="address" type="text" value="{{ old('address') }}" placeholder="123 Galle Road, Colombo" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label class="block"><span data-email-label class="text-[12px] text-ink-soft">Email</span><input name="email" type="email" value="{{ old('email') }}" placeholder="you@email.com" required class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label data-field="mobile" class="block"><span class="text-[12px] text-ink-soft">Mobile number</span><input name="mobile" type="tel" value="{{ old('mobile') }}" placeholder="+94 77 123 4567" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label class="block"><span class="text-[12px] text-ink-soft">Password</span><input name="password" type="password" placeholder="••••••••" required class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>
                        <label data-field="password_confirmation" class="block"><span class="text-[12px] text-ink-soft">Confirm password</span><input name="password_confirmation" type="password" placeholder="••••••••" class="mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"></label>

                        @if ($errors->any())
                            <ul class="rounded-2xl bg-secondary px-4 py-3 text-[12px] text-red-700">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        @endif

                        <div data-login-options class="flex items-center justify-between text-[12px] text-ink-soft"><label class="flex items-center gap-2"><input type="checkbox" name="remember" class="accent-teal-deep"> Keep me signed in</label><button type="button" class="transition-colors hover:text-teal-deep">Forgot password?</button></div>
                        <label data-register-options class="flex items-start gap-2.5 text-[12px] leading-relaxed text-ink-soft"><input type="checkbox" required class="mt-0.5 accent-teal-deep"> I agree to PsyCare's clinical privacy policy and consent to secure storage of my records.</label>
                        <button id="account-submit" type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5"></button>
                    </form>
                    <p class="mt-6 text-[12px] text-ink-soft">Looking for a clinician instead? <a href="{{ route('doctors.index') }}" class="text-teal-deep underline-offset-4 hover:underline">Browse doctors</a></p>
                </section>
            </div>
        </main>
    </div>

    @php
        $accountRoutes = [
            'patient' => ['login' => route('login'), 'register' => route('register')],
            'clinic' => ['login' => route('medical-center.login'), 'register' => route('medical-center.register')],
        ];
    @endphp
    <script>
        (() => {
            const root = document.getElementById('account-access');
            const form = document.getElementById('account-form');
            const heading = document.getElementById('account-heading');
            const submit = document.getElementById('account-submit');
            const routes = {{ Illuminate\Support\Js::from($accountRoutes) }};
            let role = root.dataset.role;
            let mode = root.dataset.mode;

            const update = () => {
                const isClinic = role === 'clinic';
                const isRegister = mode === 'register';
                heading.textContent = isRegister ? 'Create your PsyCare account' : 'Welcome back to PsyCare';
                form.action = routes[role][mode];
                submit.textContent = `${isRegister ? 'Register' : 'Log in'} as ${isClinic ? 'clinic' : 'patient'}`;
                document.querySelector('[data-name-label]').textContent = isClinic ? 'Clinic name' : 'Full name';
                document.querySelector('[data-email-label]').textContent = isClinic ? 'Clinic email' : 'Email';
                form.querySelector('[name="name"]').placeholder = isClinic ? 'Serene Mind Clinic' : 'Amaya Silva';
                form.querySelector('[name="email"]').placeholder = isClinic ? 'admin@clinic.lk' : 'you@email.com';

                document.querySelectorAll('[data-role-button]').forEach((button) => {
                    const active = button.dataset.roleButton === role;
                    button.className = active ? 'rounded-2xl bg-ink p-4 text-left text-primary-foreground' : 'rounded-2xl bg-secondary p-4 text-left text-ink-soft transition-colors hover:text-ink';
                    button.setAttribute('aria-pressed', active.toString());
                });
                document.querySelectorAll('[data-mode-button]').forEach((button) => {
                    const active = button.dataset.modeButton === mode;
                    button.className = active ? 'rounded-full bg-card px-5 py-2 text-[12px] font-medium text-ink' : 'rounded-full px-5 py-2 text-[12px] text-ink-soft transition-colors hover:text-ink';
                });

                const visibility = { name: isRegister, registration_number: isRegister && isClinic, phone: isRegister && isClinic, address: isRegister && isClinic, mobile: isRegister && !isClinic, password_confirmation: isRegister };
                Object.entries(visibility).forEach(([field, visible]) => {
                    const wrapper = document.querySelector(`[data-field="${field}"]`);
                    wrapper.hidden = !visible;
                    wrapper.querySelector('input').required = visible;
                });
                document.querySelector('[data-login-options]').hidden = isRegister;
                document.querySelector('[data-register-options]').hidden = !isRegister;
            };

            document.querySelectorAll('[data-role-button]').forEach((button) => button.addEventListener('click', () => { role = button.dataset.roleButton; update(); }));
            document.querySelectorAll('[data-mode-button]').forEach((button) => button.addEventListener('click', () => { mode = button.dataset.modeButton; update(); }));
            update();
        })();
    </script>
</body>
</html>
