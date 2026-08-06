@extends('layouts.site')

@section('title', 'Log In | PsyCare')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16">
        <h1 class="mb-6 text-2xl font-semibold">Log in to your account</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
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
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Sign up</a>
        </p>
    </div>
@endsection
