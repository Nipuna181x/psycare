@extends('layouts.doctor')

@php
    $title = 'Group Sessions';
    $subtitle = 'Anonymous peer support rooms you host';
@endphp

@section('content')
    <div class="grid gap-5">
        <div class="flex justify-end">
            <a href="{{ route('doctor.therapy-rooms.create') }}" class="rounded-2xl bg-ink px-5 py-3 text-[11px] font-semibold tracking-[0.12em] text-primary-foreground uppercase transition-transform hover:-translate-y-0.5">Schedule a room</a>
        </div>

        <x-dashboard.panel title="Upcoming" :subtitle="$upcoming->count().' room'.($upcoming->count() === 1 ? '' : 's')">
            <ul class="divide-y divide-border">
                @forelse ($upcoming as $room)
                    @include('doctor.therapy-rooms._row', ['room' => $room])
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No group sessions scheduled yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>

        <x-dashboard.panel title="History" subtitle="Completed & cancelled">
            <ul class="divide-y divide-border">
                @forelse ($history as $room)
                    @include('doctor.therapy-rooms._row', ['room' => $room])
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No past group sessions yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>
    </div>
@endsection
