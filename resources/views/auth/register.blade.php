@extends('layouts.site')

@section('title', 'Sign Up | PsyCare')

@section('content')
    <div class="mx-auto max-w-md px-4 py-16">
        <h1 class="mb-6 text-2xl font-semibold">Create your account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
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
                <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile Number</label>
                <input id="mobile" name="mobile" type="text" value="{{ old('mobile') }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('mobile')
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

            <button type="submit"
                class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                Sign Up
            </button>
        </form>

        <p class="mt-4 text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Log in</a>
        </p>
    </div>
@endsection
