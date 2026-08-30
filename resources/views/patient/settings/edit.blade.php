<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Settings — PsyCare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $inputClasses = 'mt-1.5 w-full rounded-2xl bg-secondary px-4 py-3.5 text-[13px] text-ink outline-none focus:ring-2 focus:ring-ring';
    @endphp
    <div class="min-h-screen bg-background text-ink">
        <x-patient-nav />

        <main class="mx-auto max-w-[1000px] px-5 pb-24 md:px-9">
            <header>
                <p class="eyebrow">Account</p>
                <h1 class="display-head mt-2 text-[clamp(1.8rem,3.6vw,2.6rem)] text-ink">Settings</h1>
            </header>

            @if (session('status'))
                <div class="mt-4 rounded-2xl bg-sky-50 px-4 py-3 text-[13px] text-sky-700">{{ session('status') }}</div>
            @endif

            <div class="mt-8 grid items-start gap-5 xl:grid-cols-2">
                <section class="rounded-3xl bg-card p-6 md:p-8">
                    <h2 class="font-display text-[16px] font-medium text-ink">Profile</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">Your basic account information.</p>

                    <form method="POST" action="{{ route('settings.profile.update') }}" class="mt-6 space-y-4">
                        @csrf
                        @method('PATCH')
                        <label class="block"><span class="text-[12px] text-ink-soft">Full name</span>
                            <input name="name" type="text" value="{{ old('name', $patient->name) }}" required class="{{ $inputClasses }}">
                            @error('name')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="block"><span class="text-[12px] text-ink-soft">Email</span>
                            <input name="email" type="email" value="{{ old('email', $patient->email) }}" required class="{{ $inputClasses }}">
                            @error('email')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="block"><span class="text-[12px] text-ink-soft">Mobile number</span>
                            <input name="mobile" type="tel" value="{{ old('mobile', $patient->mobile) }}" class="{{ $inputClasses }}">
                            @error('mobile')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <button type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Save profile</button>
                    </form>
                </section>

                <section class="rounded-3xl bg-card p-6 md:p-8">
                    <h2 class="font-display text-[16px] font-medium text-ink">Change password</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">Use a unique password you don't use elsewhere.</p>

                    <form method="POST" action="{{ route('settings.password.update') }}" class="mt-6 space-y-4">
                        @csrf
                        @method('PATCH')
                        <label class="block"><span class="text-[12px] text-ink-soft">Current password</span>
                            <input name="current_password" type="password" required autocomplete="current-password" class="{{ $inputClasses }}">
                            @error('current_password')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="block"><span class="text-[12px] text-ink-soft">New password</span>
                            <input name="password" type="password" required autocomplete="new-password" class="{{ $inputClasses }}">
                            @error('password')<span class="mt-1.5 block text-[12px] text-red-700">{{ $message }}</span>@enderror
                        </label>
                        <label class="block"><span class="text-[12px] text-ink-soft">Confirm new password</span>
                            <input name="password_confirmation" type="password" required autocomplete="new-password" class="{{ $inputClasses }}">
                        </label>
                        <button type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Update password</button>
                    </form>
                </section>

                <section class="rounded-3xl bg-card p-6 md:p-8 xl:col-span-2">
                    <h2 class="font-display text-[16px] font-medium text-ink">Care access</h2>
                    <p class="mt-1 max-w-[65ch] text-[12px] text-ink-soft">Control which of your doctors can see your appointment and prescription history from other clinics. Doctors can always see the visits you've had with them, regardless of this setting.</p>

                    <div class="mt-6 divide-y divide-border">
                        @forelse ($doctors as $doctor)
                            @php($consent = $doctor->consentsReceived->first())
                            @php($granted = $consent && $consent->isActive())
                            <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-medium text-ink">Dr. {{ $doctor->name }}</p>
                                    <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $doctor->specialization ?? 'General practice' }}</p>
                                </div>
                                <form method="POST" action="{{ route('settings.care-access.update', $doctor) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="grant" value="{{ $granted ? '0' : '1' }}">
                                    <button type="submit" class="shrink-0 rounded-xl {{ $granted ? 'border border-red-200 bg-white text-red-700 hover:bg-red-50' : 'bg-sky-700 text-white hover:bg-sky-800' }} px-4 py-2.5 text-[11px] font-semibold tracking-[0.08em] uppercase transition-colors">
                                        {{ $granted ? 'Revoke access' : 'Grant access' }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="py-2 text-[13px] text-ink-soft">You have no doctors on file yet.</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
