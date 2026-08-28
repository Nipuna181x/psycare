<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — PsyCare Clinic Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-ink selection:bg-purple-500/15">
    <div class="flex min-h-screen gap-5 p-3 md:p-5 lg:h-dvh lg:overflow-hidden">
        <x-dashboard.sidebar
            accent="clinic"
            role-label="Clinic portal"
            :logout-action="route('medical-center.logout')"
            :links="[
                [
                    'label' => 'Dashboard',
                    'href' => route('medical-center.dashboard'),
                    'active' => request()->routeIs('medical-center.dashboard'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect width=\'7\' height=\'9\' x=\'3\' y=\'3\' rx=\'1\'/><rect width=\'7\' height=\'5\' x=\'14\' y=\'3\' rx=\'1\'/><rect width=\'7\' height=\'9\' x=\'14\' y=\'12\' rx=\'1\'/><rect width=\'7\' height=\'5\' x=\'3\' y=\'16\' rx=\'1\'/></svg>',
                ],
                [
                    'label' => 'Find Doctors',
                    'href' => route('medical-center.find-doctors.index'),
                    'active' => request()->routeIs('medical-center.find-doctors.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'11\' cy=\'11\' r=\'8\'/><path d=\'m21 21-4.3-4.3\'/></svg>',
                ],
                [
                    'label' => 'Pending Requests',
                    'href' => route('medical-center.affiliations.index'),
                    'active' => request()->routeIs('medical-center.affiliations.*'),
                    'badge' => auth('medical_center')->user()->affiliations()->where('status', 'requested')->count(),
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M22 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>',
                ],
                [
                    'label' => 'Appointments',
                    'href' => route('medical-center.appoinment-managment.index'),
                    'active' => request()->routeIs('medical-center.appoinment-managment.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M8 2v4M16 2v4M3 10h18\'/><rect width=\'18\' height=\'18\' x=\'3\' y=\'4\' rx=\'2\'/></svg>',
                ],
            ]"
            promo-title="Grow your team"
            promo-description="Search doctors by licence number and send them a work request to join your clinic."
            promo-cta-label="Find doctors"
            :promo-cta-href="route('medical-center.find-doctors.index')"
        />

        <div class="flex min-w-0 flex-1 flex-col gap-5 lg:h-[calc(100dvh-2.5rem)] lg:overflow-y-auto">
            <x-dashboard.topbar
                accent="clinic"
                :title="$title ?? 'Dashboard'"
                :subtitle="$subtitle ?? null"
                :user-name="auth('medical_center')->user()->name"
                role-label="Clinic"
            />

            <main class="flex-1">
                @if (session('status'))
                    <div class="mb-5 rounded-2xl bg-purple-50 px-4 py-3 text-[13px] text-purple-700">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
