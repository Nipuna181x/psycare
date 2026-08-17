@extends('layouts.doctor')

@php
    $title = 'Appointments';
    $subtitle = 'Everyone booked in with you';
@endphp

@section('content')
    <div class="grid gap-5">
        <x-dashboard.panel title="Today" :subtitle="$today->count().' appointment'.($today->count() === 1 ? '' : 's')">
            <ul class="divide-y divide-border">
                @forelse ($today as $appointment)
                    @include('doctor.appointments._row', ['appointment' => $appointment])
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">Nothing on the calendar for today.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <x-dashboard.panel title="Upcoming" subtitle="Confirmed appointments ahead">
            <ul class="divide-y divide-border">
                @forelse ($upcoming as $appointment)
                    @include('doctor.appointments._row', ['appointment' => $appointment])
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No upcoming appointments.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <x-dashboard.panel title="History" subtitle="Completed & cancelled">
            <ul class="divide-y divide-border">
                @forelse ($history as $appointment)
                    @include('doctor.appointments._row', ['appointment' => $appointment])
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No past appointments yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>
    </div>
@endsection
