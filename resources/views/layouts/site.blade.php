<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PsyCare')</title>
    @include('partials.favicon')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-900">
    <x-navbar />

    <main class="flex-1">
        @if (session('status'))
            <div class="mx-auto mt-4 max-w-5xl rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <x-footer />
</body>
</html>
