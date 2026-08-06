<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clinic Registration | PsyCare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 text-gray-900">
    <div class="w-full max-w-lg rounded-lg border border-gray-200 bg-white p-8">
        <h1 class="mb-6 text-2xl font-semibold">Register your Clinic / Hospital</h1>

        <form method="POST" action="{{ route('medical-center.register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Medical Center Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <input id="address" name="address" type="text" value="{{ old('address') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="registration_number" class="block text-sm font-medium text-gray-700">Registration Number</label>
                <input id="registration_number" name="registration_number" type="text" value="{{ old('registration_number') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('registration_number')
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

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>

            <p class="text-xs text-gray-500">
                Your account will be reviewed by an admin before you can log in.
            </p>

            <button type="submit"
                class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Register
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Already registered?
            <a href="{{ route('medical-center.login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Log in</a>
        </p>
    </div>
</body>
</html>
