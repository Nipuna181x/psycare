@extends('layouts.doctor')

@php
    $title = 'Patients';
    $subtitle = 'Everyone you have treated';
@endphp

@section('content')
    <div class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
        <form method="GET" action="{{ route('doctor.patients.index') }}" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Patient name</span>
                <input type="text" name="name" value="{{ $filters['name'] }}" placeholder="Amaya Silva" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            </label>
            <label class="block">
                <span class="text-[11px] font-medium text-ink-soft">Risk level</span>
                <select name="risk" class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    <option value="">All risk levels</option>
                    @foreach (['low' => 'Low', 'moderate' => 'Moderate', 'elevated' => 'Elevated'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['risk'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="mt-1.5 self-end rounded-xl bg-blue-800 px-6 py-2.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Filter</button>
        </form>
    </div>

    <div class="mt-5">
        <x-dashboard.panel title="All patients" :subtitle="$patients->count().' '.Str::plural('patient', $patients->count())">
        @if ($patients->isEmpty())
            <p class="py-6 text-center text-[13px] text-ink-soft">
                @if ($filters['name'] !== '' || $filters['risk'])
                    No patients match your search.
                @else
                    No patients yet — they'll appear here once someone books an appointment with you.
                @endif
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-[12px]">
                    <thead>
                        <tr class="text-[11px] tracking-[0.06em] text-ink-soft uppercase">
                            <th class="py-2 pr-4">Patient</th>
                            <th class="py-2 pr-4">Contact</th>
                            <th class="py-2 pr-4">Appointments</th>
                            <th class="py-2 pr-4">Lumi report</th>
                            <th class="py-2 pr-4">Risk level</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($patients as $patient)
                            @php($report = $patient->patientNlpReports->first())
                            <tr>
                                <td class="py-3 pr-4 font-medium whitespace-nowrap text-ink">{{ $patient->name }}</td>
                                <td class="py-3 pr-4 text-ink-soft">
                                    <p>{{ $patient->mobile }}</p>
                                    <p class="text-[11px]">{{ $patient->email }}</p>
                                </td>
                                <td class="py-3 pr-4 text-ink">{{ $patient->appointments_count }}</td>
                                <td class="py-3 pr-4">
                                    @if ($report)
                                        <span class="text-emerald-700">Available</span>
                                    @else
                                        <span class="text-ink-soft">Not used</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if ($report && ($report->report['risk']['level'] ?? null))
                                        <x-dashboard.badge :status="$report->report['risk']['level']" />
                                    @else
                                        <span class="text-[12px] text-ink-soft">—</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('doctor.patients.show', $patient) }}" class="text-[11px] font-semibold tracking-[0.08em] text-teal-deep uppercase hover:underline">View profile</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        </x-dashboard.panel>
    </div>
@endsection
