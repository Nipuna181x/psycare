@extends('layouts.medical-center')

@php
    $title = 'Pending Requests';
    $subtitle = 'Work requests you have sent to doctors';
@endphp

@section('content')
    <x-dashboard.panel title="Requests sent">
        <ul class="divide-y divide-border">
            @forelse ($affiliations as $affiliation)
                <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-purple-100 text-[12px] font-semibold text-purple-700">{{ mb_strtoupper(mb_substr($affiliation->doctor->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-[13px] font-medium text-ink">{{ $affiliation->doctor->name }}</p>
                            <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $affiliation->doctor->specialization ?? 'General practice' }} · Sent {{ $affiliation->requested_by_clinic_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                    <x-dashboard.badge :status="$affiliation->status" />
                </li>
            @empty
                <li class="py-3.5 text-[12px] text-ink-soft">You haven't sent any work requests yet. <a href="{{ route('medical-center.find-doctors.index') }}" class="font-medium text-purple-700 underline-offset-4 hover:underline">Find doctors</a>.</li>
            @endforelse
        </ul>
    </x-dashboard.panel>

    <div class="mt-5">{{ $affiliations->links() }}</div>
@endsection
