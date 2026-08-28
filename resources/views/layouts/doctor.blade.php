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
                    'label' => 'Crisis Queue',
                    'href' => route('doctor.crisis-queue.index'),
                    'active' => request()->routeIs('doctor.crisis-queue.*'),
                    'badge' => $doctorEscalationCount,
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M10.3 2.86 1.82 17a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.7 2.86a2 2 0 0 0-3.4 0Z\'/><path d=\'M12 9v4\'/><path d=\'M12 17h.01\'/></svg>',
                ],
                [
                    'label' => 'Clinic Requests',
                    'href' => route('doctor.clinic-requests.index'),
                    'active' => request()->routeIs('doctor.clinic-requests.*'),
                    'badge' => $doctorPendingClinicRequestCount,
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z\'/><path d=\'M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2\'/><path d=\'M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2\'/><path d=\'M10 6h4\'/><path d=\'M10 10h4\'/><path d=\'M10 14h4\'/><path d=\'M10 18h4\'/></svg>',
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
                [
                    'label' => 'Notifications',
                    'href' => route('doctor.notifications.index'),
                    'active' => request()->routeIs('doctor.notifications.*'),
                    'badge' => $doctorUnreadNotificationCount,
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9\'/><path d=\'M10.3 21a1.94 1.94 0 0 0 3.4 0\'/></svg>',
                ],
            ]"
            :show-logout="false"
        />

        <div class="flex flex-1 flex-col gap-5">
            <x-dashboard.topbar
                class="print:hidden"
                accent="doctor"
                :title="$title ?? 'Dashboard'"
                :subtitle="$subtitle ?? null"
                :user-name="'Dr. '.auth('doctor')->user()->name"
                role-label="Doctor"
                :notifications="$doctorHeaderNotifications"
                :notification-count="$doctorUnreadNotificationCount"
                :notifications-route="route('doctor.notifications.index')"
                notification-read-route-name="doctor.notifications.read"
                :profile-href="route('doctor.profile.edit')"
                :logout-action="route('doctor.logout')"
                :avatar-url="auth('doctor')->user()->avatarUrl()"
            >
                @if ($doctorRequiresClinicSwitcher)
                    <x-slot:contextSwitcher>
                        <x-dashboard.clinic-switcher :clinics="$doctorActiveClinics" :active-clinic-id="$doctorActiveClinicId" />
                    </x-slot:contextSwitcher>
                @endif
            </x-dashboard.topbar>

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
