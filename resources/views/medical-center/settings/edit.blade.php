@extends('layouts.medical-center')

@php
    $title = 'Settings';
    $subtitle = 'Manage your clinic pricing';
@endphp

@section('content')
    <section class="max-w-xl rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
        <h2 class="font-display text-[16px] font-medium text-ink">Facility fee</h2>
        <p class="mt-1 text-[11px] text-ink-soft">A single flat fee charged to every patient booking with any doctor at your clinic. This is separate from — and in addition to — the doctor's own session fee.</p>
        <form method="POST" action="{{ route('medical-center.settings.pricing.update') }}" class="mt-5 grid gap-4">
            @csrf @method('PATCH')
            <label class="text-[11px] font-medium text-ink">Facility fee (LKR)
                <input type="number" step="0.01" min="0" name="facility_fee" value="{{ old('facility_fee', $clinic->facility_fee) }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-100">
            </label>
            @error('facility_fee')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
            <button class="justify-self-start rounded-xl bg-purple-700 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-purple-800">Save pricing</button>
        </form>
    </section>
@endsection
