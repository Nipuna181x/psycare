@extends('layouts.medical-center')

@php
    $title = 'Patients';
    $subtitle = 'Everyone treated at your clinic, across all your doctors';
@endphp

@section('content')
    <div class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
        <form method="GET" action="{{ route('medical-center.patients.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Patient name</span>
                <input type="text" name="name" value="{{ $filters['name'] }}" placeholder="Amaya Silva" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Doctor</span>
                <select name="doctor_id" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">All doctors</option>
                    @foreach ($doctorOptions as $doctor)
                        <option value="{{ $doctor->id }}" @selected($filters['doctor_id'] == $doctor->id)>{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="mt-1.5 self-end rounded-xl bg-blue-800 px-6 py-2.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Filter</button>
        </form>
    </div>

    <div class="mt-5">
        <x-dashboard.panel title="All patients">
            <ul class="divide-y divide-border">
                @forelse ($patients as $patient)
                    @php $latest = $patient->appointments->first(); @endphp
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <a href="{{ route('medical-center.patients.show', $patient) }}" class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-100 text-[12px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($patient->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $patient->name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">
                                    @if ($latest?->patient_age) {{ $latest->patient_age }} yrs @endif
                                    @if ($latest?->patient_gender) · {{ ucfirst($latest->patient_gender) }} @endif
                                    @if ($latest) · Last seen {{ $latest->appointment_date->format('D, j M Y') }} with {{ $latest->doctor->name }} @endif
                                </p>
                            </div>
                        </a>
                        <span class="shrink-0 rounded-full bg-secondary px-2.5 py-1 text-[10px] font-semibold text-ink-soft">{{ $patient->appointments_count }} appointment(s)</span>
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No patients match yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <div class="mt-5">{{ $patients->links() }}</div>
    </div>
@endsection
