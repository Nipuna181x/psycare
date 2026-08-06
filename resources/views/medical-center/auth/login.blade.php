<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clinic Portal Login | PsyCare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 text-gray-900">
    <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-8">
        <h1 class="mb-6 text-2xl font-semibold">Clinic Portal Login</h1>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('medical-center.login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember">
                Remember me
            </label>

            <button type="submit"
                class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Log In
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            New clinic or hospital?
            <a href="{{ route('medical-center.register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Register here</a>
        </p>
    </div>
</body>
</html>
