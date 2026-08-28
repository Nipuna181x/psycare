@extends('layouts.admin')

@section('title', 'Doctor Approvals')

@section('content')
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Doctor</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Licence No.</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Specialization</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($doctors as $doctor)
                    <tr>
                        <td class="px-4 py-3">{{ $doctor->name }}</td>
                        <td class="px-4 py-3">{{ $doctor->email }}</td>
                        <td class="px-4 py-3">{{ $doctor->license_number }}</td>
                        <td class="px-4 py-3">{{ $doctor->specialization ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('admin.doctor-approvals.approve', $doctor) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md border border-green-300 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-50">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.doctor-approvals.reject', $doctor) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-50">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No doctor applications awaiting approval.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $doctors->links() }}
    </div>
@endsection
