<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — PsyCare Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-ink selection:bg-teal/20">
    <div class="flex min-h-screen gap-5 p-3 md:p-5">
        <x-dashboard.sidebar
            accent="admin"
            role-label="Admin console"
            :logout-action="route('admin.logout')"
            :links="[
                [
                    'label' => 'Dashboard',
                    'href' => route('admin.dashboard'),
                    'active' => request()->routeIs('admin.dashboard'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect width=\'7\' height=\'9\' x=\'3\' y=\'3\' rx=\'1\'/><rect width=\'7\' height=\'5\' x=\'14\' y=\'3\' rx=\'1\'/><rect width=\'7\' height=\'9\' x=\'14\' y=\'12\' rx=\'1\'/><rect width=\'7\' height=\'5\' x=\'3\' y=\'16\' rx=\'1\'/></svg>',
                ],
                [
                    'label' => 'Medical Centers',
                    'href' => route('admin.user-managment.index'),
                    'active' => request()->routeIs('admin.user-managment.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z\'/><path d=\'M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2\'/><path d=\'M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2\'/><path d=\'M10 6h4\'/><path d=\'M10 10h4\'/><path d=\'M10 14h4\'/><path d=\'M10 18h4\'/></svg>',
                ],
            ]"
            promo-title="Platform integrity"
            promo-description="Review pending medical centre applications to keep the network trustworthy."
            promo-cta-label="Review approvals"
            :promo-cta-href="route('admin.user-managment.index')"
        />

        <div class="flex flex-1 flex-col gap-5">
            <x-dashboard.topbar
                accent="admin"
                :title="$title ?? 'Dashboard'"
                :subtitle="$subtitle ?? null"
                :user-name="auth('admin')->user()->name"
                role-label="Super Admin"
            />

            <main class="flex-1">
                @if (session('status'))
                    <div class="mb-5 rounded-2xl bg-secondary px-4 py-3 text-[13px] text-ink-soft">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
