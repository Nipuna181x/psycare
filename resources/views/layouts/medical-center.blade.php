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
<body class="bg-background text-ink selection:bg-blue-800/15">
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
                    'label' => 'Doctors',
                    'href' => route('medical-center.doctors.index'),
                    'active' => request()->routeIs('medical-center.doctors.*'),
                    'badge' => $clinicPendingRequestCount ?? 0,
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><circle cx=\'11\' cy=\'11\' r=\'8\'/><path d=\'m21 21-4.3-4.3\'/></svg>',
                ],
                [
                    'label' => 'Patients',
                    'href' => route('medical-center.patients.index'),
                    'active' => request()->routeIs('medical-center.patients.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2\'/><circle cx=\'12\' cy=\'7\' r=\'4\'/></svg>',
                ],
                [
                    'label' => 'Appointments',
                    'href' => route('medical-center.appoinment-managment.index'),
                    'active' => request()->routeIs('medical-center.appoinment-managment.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M8 2v4M16 2v4M3 10h18\'/><rect width=\'18\' height=\'18\' x=\'3\' y=\'4\' rx=\'2\'/></svg>',
                ],
                [
                    'label' => 'Analytics',
                    'href' => route('medical-center.analytics.index'),
                    'active' => request()->routeIs('medical-center.analytics.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M3 3v18h18\'/><path d=\'M18 17V9\'/><path d=\'M13 17V5\'/><path d=\'M8 17v-3\'/></svg>',
                ],
                [
                    'label' => 'Settings',
                    'href' => route('medical-center.settings.edit'),
                    'active' => request()->routeIs('medical-center.settings.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/></svg>',
                ],
                [
                    'label' => 'Notifications',
                    'href' => route('medical-center.notifications.index'),
                    'active' => request()->routeIs('medical-center.notifications.*'),
                    'badge' => $clinicUnreadNotificationCount ?? 0,
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9\'/><path d=\'M10.3 21a1.94 1.94 0 0 0 3.4 0\'/></svg>',
                ],
                ...(auth('medical_center')->check() ? [[
                    'label' => 'Clinic Staff',
                    'href' => route('medical-center.staff.index'),
                    'active' => request()->routeIs('medical-center.staff.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\'/><circle cx=\'9\' cy=\'7\' r=\'4\'/><path d=\'M23 21v-2a4 4 0 0 0-3-3.87\'/><path d=\'M16 3.13a4 4 0 0 1 0 7.75\'/></svg>',
                ]] : []),
            ]"
        />

        <div class="flex min-w-0 flex-1 flex-col gap-5 lg:h-[calc(100dvh-2.5rem)] lg:overflow-y-auto">
            <x-dashboard.topbar
                accent="clinic"
                :title="$title ?? 'Dashboard'"
                :subtitle="$subtitle ?? null"
                :user-name="$currentActorLabel ?? ''"
                role-label="Clinic"
                :notifications="$clinicHeaderNotifications ?? collect()"
                :notification-count="$clinicUnreadNotificationCount ?? 0"
                :notifications-route="route('medical-center.notifications.index')"
                notification-read-route-name="medical-center.notifications.read"
            />

            <main class="flex-1">
                @if (session('status'))
                    <div class="mb-5 rounded-2xl bg-blue-50 px-4 py-3 text-[13px] text-blue-800">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
