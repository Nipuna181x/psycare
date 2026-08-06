<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Doctor Portal | PsyCare')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4">
        <span class="text-lg font-semibold text-gray-900">PsyCare &mdash; Doctor Portal</span>

        <form method="POST" action="{{ route('doctor.logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                Logout
            </button>
        </form>
    </header>

    <main class="p-6">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
