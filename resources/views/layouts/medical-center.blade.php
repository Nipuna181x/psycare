<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Clinic Portal | PsyCare')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <div class="flex min-h-screen">
        <x-sidebar :links="[
            ['label' => 'Dashboard', 'href' => route('medical-center.dashboard'), 'active' => request()->routeIs('medical-center.dashboard')],
            ['label' => 'Doctor Management', 'href' => route('medical-center.doctor-managment.index'), 'active' => request()->routeIs('medical-center.doctor-managment.*')],
        ]" />

        <div class="flex flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
                <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>

                <form method="POST" action="{{ route('medical-center.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        Logout
                    </button>
                </form>
            </header>

            <main class="flex-1 p-6">
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
