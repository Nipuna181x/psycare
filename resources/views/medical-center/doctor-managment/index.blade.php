@extends('layouts.medical-center')

@section('title', 'Doctor Management')

@section('content')
    <div class="mb-4 flex justify-end">
        <a href="{{ route('medical-center.doctor-managment.create') }}"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
            Add Doctor
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Username</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Specialization</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($doctors as $doctor)
                    <tr>
                        <td class="px-4 py-3">{{ $doctor->name }}</td>
                        <td class="px-4 py-3">{{ $doctor->username }}</td>
                        <td class="px-4 py-3">{{ $doctor->specialization ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'rounded-full px-2 py-1 text-xs font-medium',
                                'bg-green-100 text-green-800' => $doctor->status === 'active',
                                'bg-gray-100 text-gray-800' => $doctor->status === 'inactive',
                            ])>
                                {{ ucfirst($doctor->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('medical-center.doctor-managment.edit', $doctor) }}"
                                    class="rounded-md border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('medical-center.doctor-managment.destroy', $doctor) }}"
                                    onsubmit="return confirm('Remove this doctor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No doctors added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $doctors->links() }}
    </div>
@endsection
