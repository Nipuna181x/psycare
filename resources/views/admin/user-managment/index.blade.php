@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Medical Center</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Registration No.</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($medicalCenters as $medicalCenter)
                    <tr>
                        <td class="px-4 py-3">{{ $medicalCenter->name }}</td>
                        <td class="px-4 py-3">{{ $medicalCenter->email }}</td>
                        <td class="px-4 py-3">{{ $medicalCenter->registration_number }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'rounded-full px-2 py-1 text-xs font-medium',
                                'bg-yellow-100 text-yellow-800' => $medicalCenter->status === 'pending',
                                'bg-green-100 text-green-800' => $medicalCenter->status === 'approved',
                                'bg-red-100 text-red-800' => $medicalCenter->status === 'rejected',
                            ])>
                                {{ ucfirst($medicalCenter->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('admin.user-managment.medical-centers.approve', $medicalCenter) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @disabled($medicalCenter->status === 'approved')
                                        class="rounded-md border border-green-300 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-50 disabled:opacity-50">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.user-managment.medical-centers.reject', $medicalCenter) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @disabled($medicalCenter->status === 'rejected')
                                        class="rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-50 disabled:opacity-50">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No medical centers have registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $medicalCenters->links() }}
    </div>
@endsection
