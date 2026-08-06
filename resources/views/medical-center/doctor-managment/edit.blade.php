@extends('layouts.medical-center')

@section('title', 'Edit Doctor')

@section('content')
    <div class="max-w-lg rounded-lg border border-gray-200 bg-white p-6">
        <form method="POST" action="{{ route('medical-center.doctor-managment.update', $doctor) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $doctor->name) }}" required autofocus
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $doctor->email) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="specialization" class="block text-sm font-medium text-gray-700">Specialization</label>
                <input id="specialization" name="specialization" type="text" value="{{ old('specialization', $doctor->specialization) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('specialization')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $doctor->phone) }}"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    <option value="active" @selected(old('status', $doctor->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $doctor->status) === 'inactive')>Inactive</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-gray-200">

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username', $doctor->username) }}" required
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                @error('username')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input id="password" name="password" type="password"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password.</p>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('medical-center.doctor-managment.index') }}"
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection
