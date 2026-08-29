@extends('layouts.medical-center')

@php
    $title = 'Appointments';
    $subtitle = 'Every booking across your doctors';
@endphp

@section('content')
    <div class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
        <form method="GET" action="{{ route('medical-center.appoinment-managment.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_auto_auto_auto]">
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Patient name</span>
                <input type="text" name="name" value="{{ $filters['name'] }}" placeholder="Amaya Silva" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">From date</span>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">To date</span>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-xl bg-blue-800 px-6 py-2.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Filter</button>
                @if ($filters['name'] || $filters['date_from'] || $filters['date_to'])
                    <a href="{{ route('medical-center.appoinment-managment.index') }}" class="rounded-xl border border-border px-4 py-2.5 text-[11px] font-semibold tracking-[0.08em] text-ink-soft uppercase hover:bg-secondary">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="mt-5">
        <x-dashboard.panel title="All appointments">
            <ul class="divide-y divide-border">
                @forelse ($appointments as $appointment)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <a href="{{ route('medical-center.appoinment-managment.show', $appointment) }}" class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-100 text-[12px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($appointment->patient_name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $appointment->patient_name }} <span class="text-ink-soft">with</span> {{ $appointment->doctor->name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $appointment->appointment_date->format('D, j M Y') }} · {{ \Illuminate\Support\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                            </div>
                        </a>
                        <div class="flex items-center gap-2">
                            @if ($appointment->pre_assessment_risk_level)
                                <x-dashboard.badge :status="$appointment->pre_assessment_risk_level" />
                            @endif
                            <x-dashboard.badge :status="$appointment->status" />
                        </div>
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">
                        @if ($filters['name'] || $filters['date_from'] || $filters['date_to'])
                            No appointments match your filters.
                        @else
                            No appointments booked yet.
                        @endif
                    </li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <div class="mt-5">{{ $appointments->links() }}</div>
    </div>
@endsection
