<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} — PsyCare Doctor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600&family=DM+Sans:opsz,wght@9..40,300..600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-ink selection:bg-sky-500/15">
    <div class="flex min-h-screen gap-5 p-3 md:p-5">
        <x-dashboard.sidebar
            class="print:hidden"
            accent="doctor"
            role-label="Doctor portal"
            :logout-action="route('doctor.logout')"
            :links="[
                [
                    'label' => 'Dashboard',
                    'href' => route('doctor.dashboard'),
                    'active' => request()->routeIs('doctor.dashboard'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect width=\'7\' height=\'9\' x=\'3\' y=\'3\' rx=\'1\'/><rect width=\'7\' height=\'5\' x=\'14\' y=\'3\' rx=\'1\'/><rect width=\'7\' height=\'9\' x=\'14\' y=\'12\' rx=\'1\'/><rect width=\'7\' height=\'5\' x=\'3\' y=\'16\' rx=\'1\'/></svg>',
                ],
                [
                    'label' => 'Appointments',
                    'href' => route('doctor.appointments.index'),
                    'active' => request()->routeIs('doctor.appointments.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M8 2v4M16 2v4M3 10h18\'/><rect width=\'18\' height=\'18\' x=\'3\' y=\'4\' rx=\'2\'/></svg>',
                ],
                [
                    'label' => 'Patients',
                    'href' => route('doctor.patients.index'),
                    'active' => request()->routeIs('doctor.patients.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M22 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>',
                ],
                [
                    'label' => 'Group Sessions',
                    'href' => route('doctor.therapy-rooms.index'),
                    'active' => request()->routeIs('doctor.therapy-rooms.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M23 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>',
                ],
            ]"
        />

        <div class="flex flex-1 flex-col gap-5">
            <x-dashboard.topbar
                class="print:hidden"
                accent="doctor"
                :title="$title ?? 'Dashboard'"
                :subtitle="$subtitle ?? null"
                :user-name="'Dr. '.auth('doctor')->user()->name"
                role-label="Doctor"
            />

            <main class="flex-1">
                @if (session('status'))
                    <div class="mb-5 rounded-2xl bg-sky-50 px-4 py-3 text-[13px] text-sky-700">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
