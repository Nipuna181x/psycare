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
    <div class="flex min-h-screen gap-5 p-3 md:p-5 lg:h-dvh lg:overflow-hidden">
        <x-dashboard.sidebar
            class="print:hidden"
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
                    'href' => route('admin.medical-centers.index'),
                    'active' => request()->routeIs('admin.medical-centers.*') || request()->routeIs('admin.user-managment.*'),
                    'badge' => \App\Models\MedicalCenter::where('status', 'pending')->count(),
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z\'/><path d=\'M6 12H4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h2\'/><path d=\'M18 9h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2\'/><path d=\'M10 6h4\'/><path d=\'M10 10h4\'/><path d=\'M10 14h4\'/><path d=\'M10 18h4\'/></svg>',
                ],
                [
                    'label' => 'Doctors',
                    'href' => route('admin.doctors.index'),
                    'active' => request()->routeIs('admin.doctors.*') || request()->routeIs('admin.doctor-approvals.*'),
                    'badge' => \App\Models\Doctor::where('status', 'pending_approval')->where('onboarding_step', 'profile_complete')->count(),
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M8 2v4M16 2v4M3 10h18\'/><rect width=\'18\' height=\'18\' x=\'3\' y=\'4\' rx=\'2\'/><path d=\'m9 16 2 2 4-4\'/></svg>',
                ],
                [
                    'label' => 'Notifications',
                    'href' => route('admin.notifications.index'),
                    'active' => request()->routeIs('admin.notifications.*'),
                    'badge' => $adminUnreadNotificationCount ?? 0,
                    'badgeTone' => 'danger',
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9\'/><path d=\'M10.3 21a1.94 1.94 0 0 0 3.4 0\'/></svg>',
                ],
                [
                    'label' => 'Patients',
                    'href' => route('admin.patients.index'),
                    'active' => request()->routeIs('admin.patients.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M19 21v-2a7 7 0 0 0-14 0v2\'/><circle cx=\'12\' cy=\'7\' r=\'4\'/></svg>',
                ],
                [
                    'label' => 'Reports & Analytics',
                    'href' => route('admin.reports.index'),
                    'active' => request()->routeIs('admin.reports.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M3 3v18h18\'/><path d=\'m7 16 4-5 4 3 5-7\'/></svg>',
                ],
                [
                    'label' => 'SMTP Check',
                    'href' => route('admin.mail-check.index'),
                    'active' => request()->routeIs('admin.mail-check.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><rect width=\'20\' height=\'16\' x=\'2\' y=\'4\' rx=\'2\'/><path d=\'m22 7-10 6L2 7\'/><path d=\'m16 17 2 2 4-4\'/></svg>',
                ],
                [
                    'label' => 'Settings',
                    'href' => route('admin.settings.edit'),
                    'active' => request()->routeIs('admin.settings.*'),
                    'icon' => '<svg class=\'h-4 w-4\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/></svg>',
                ],
            ]"
            promo-title="Platform integrity"
            promo-description="Review pending medical centre applications to keep the network trustworthy."
            promo-cta-label="Review approvals"
            :promo-cta-href="route('admin.medical-centers.index')"
        />

        <div class="flex min-w-0 flex-1 flex-col gap-5 lg:h-[calc(100dvh-2.5rem)] lg:overflow-y-auto">
            <x-dashboard.topbar
                class="print:hidden"
                accent="admin"
                :title="$title ?? 'Dashboard'"
                :subtitle="$subtitle ?? null"
                :user-name="auth('admin')->user()->name"
                role-label="Super Admin"
                :notifications="$adminHeaderNotifications ?? collect()"
                :notification-count="$adminUnreadNotificationCount ?? 0"
                :notifications-route="route('admin.notifications.index')"
                notification-read-route-name="admin.notifications.read"
                :profile-href="route('admin.settings.edit')"
                :logout-action="route('admin.logout')"
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
