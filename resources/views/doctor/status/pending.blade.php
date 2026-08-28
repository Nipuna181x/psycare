<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Application Under Review — PsyCare Sri Lanka</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="flex min-h-screen items-center justify-center bg-background p-6 text-ink">
        <div class="w-full max-w-[520px] rounded-3xl bg-card p-8 text-center md:p-10">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-sky-100 text-sky-700">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </span>
            <p class="eyebrow mt-5">Application submitted</p>
            <h1 class="display-head mt-2 text-[clamp(1.6rem,3.4vw,2.2rem)] text-ink">Your application is under review</h1>
            <p class="mt-3 text-[13px] leading-relaxed text-ink-soft">Thanks for completing your profile. A PsyCare admin will review your details and approve your account shortly — you'll be able to log in and reach your dashboard as soon as that happens.</p>

            <form method="POST" action="{{ route('doctor.logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Log out</button>
            </form>
        </div>
    </main>
</body>
</html>
