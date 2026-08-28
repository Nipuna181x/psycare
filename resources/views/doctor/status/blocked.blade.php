<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Access Restricted — PsyCare Sri Lanka</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="flex min-h-screen items-center justify-center bg-background p-6 text-ink">
        <div class="w-full max-w-[520px] rounded-3xl bg-card p-8 text-center md:p-10">
            <span class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-red-100 text-red-700">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5"/><path d="M12 16h.01"/></svg>
            </span>
            <p class="eyebrow mt-5">Account access restricted</p>
            <h1 class="display-head mt-2 text-[clamp(1.6rem,3.4vw,2.2rem)] text-ink">
                {{ $reason === 'rejected' ? 'Your application was not approved' : 'Your account has been suspended' }}
            </h1>
            <p class="mt-3 text-[13px] leading-relaxed text-ink-soft">
                @if ($reason === 'rejected')
                    A PsyCare admin reviewed your application and it was not approved at this time. If you believe this is a mistake, please contact PsyCare support.
                @else
                    Your doctor account has been suspended and you no longer have access to the clinical portal. Please contact PsyCare support for more information.
                @endif
            </p>

            <form method="POST" action="{{ route('doctor.logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="w-full rounded-full bg-ink px-6 py-4 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Log out</button>
            </form>
        </div>
    </main>
</body>
</html>
