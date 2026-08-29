@extends('layouts.medical-center')

@php
    $title = 'Settings';
    $subtitle = 'Manage your clinic profile, contact details, hours, and pricing';
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $hours = collect($clinic->operating_hours ?: [])->keyBy('day');
@endphp

@section('content')
    <div class="columns-1 gap-5 xl:columns-2">
        <section class="mb-5 break-inside-avoid rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Profile</h2>
            <p class="mt-1 text-[11px] text-ink-soft">Your clinic name and a short public description.</p>
            <form method="POST" action="{{ route('medical-center.settings.profile.update') }}" class="mt-5 grid gap-4">
                @csrf @method('PATCH')
                <label class="text-[11px] font-medium text-ink">Clinic name
                    <input type="text" name="name" value="{{ old('name', $clinic->name) }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('name')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <label class="text-[11px] font-medium text-ink">Description
                    <textarea name="description" rows="3" class="mt-1.5 w-full resize-y rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">{{ old('description', $clinic->description) }}</textarea>
                </label>
                @error('description')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <button class="justify-self-start rounded-xl bg-blue-800 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Save profile</button>
            </form>
        </section>

        <section class="mb-5 break-inside-avoid rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Logo</h2>
            <p class="mt-1 text-[11px] text-ink-soft">Shown across the clinic portal and any patient-facing listing.</p>
            <div class="mt-4 flex items-center gap-4">
                @if ($clinic->logoUrl())
                    <img src="{{ $clinic->logoUrl() }}" alt="{{ $clinic->name }}" class="h-14 w-14 rounded-2xl object-cover">
                @else
                    <span class="grid h-14 w-14 place-items-center rounded-2xl bg-blue-100 text-[13px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($clinic->name, 0, 1)) }}</span>
                @endif
                <form method="POST" action="{{ route('medical-center.settings.logo.update') }}" enctype="multipart/form-data" class="grid gap-2">
                    @csrf @method('PATCH')
                    <input type="file" name="logo" accept="image/*" required class="text-[11px] text-ink-soft">
                    @error('logo')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                    <button class="justify-self-start rounded-xl bg-blue-800 px-5 py-2.5 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Upload logo</button>
                </form>
            </div>
        </section>

        <section class="mb-5 break-inside-avoid rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Contact</h2>
            <p class="mt-1 text-[11px] text-ink-soft">How patients and doctors can reach your clinic.</p>
            <form method="POST" action="{{ route('medical-center.settings.contact.update') }}" class="mt-5 grid gap-4">
                @csrf @method('PATCH')
                <label class="text-[11px] font-medium text-ink">Phone
                    <input type="text" name="phone" value="{{ old('phone', $clinic->phone) }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('phone')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <label class="text-[11px] font-medium text-ink">Address
                    <input type="text" name="address" value="{{ old('address', $clinic->address) }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('address')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <button class="justify-self-start rounded-xl bg-blue-800 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Save contact</button>
            </form>
        </section>

        <section class="mb-5 break-inside-avoid rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Operating hours</h2>
            <p class="mt-1 text-[11px] text-ink-soft">Set your clinic's hours for each day of the week.</p>
            <form method="POST" action="{{ route('medical-center.settings.hours.update') }}" class="mt-5 grid gap-3">
                @csrf @method('PATCH')
                @foreach ($days as $index => $day)
                    @php $row = $hours->get($day, ['opens' => null, 'closes' => null, 'closed' => false]); @endphp
                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-[1fr_auto_auto_auto] sm:items-center">
                        <input type="hidden" name="hours[{{ $index }}][day]" value="{{ $day }}">
                        <span class="col-span-2 text-[12px] font-medium text-ink sm:col-span-1">{{ $day }}</span>
                        <input type="time" name="hours[{{ $index }}][opens]" value="{{ old('hours.'.$index.'.opens', $row['opens'] ?? '') }}" class="min-w-0 rounded-xl border border-border bg-white px-2.5 py-2 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        <input type="time" name="hours[{{ $index }}][closes]" value="{{ old('hours.'.$index.'.closes', $row['closes'] ?? '') }}" class="min-w-0 rounded-xl border border-border bg-white px-2.5 py-2 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        <label class="flex items-center gap-1.5 text-[11px] text-ink-soft">
                            <input type="checkbox" name="hours[{{ $index }}][closed]" value="1" @checked(old('hours.'.$index.'.closed', $row['closed'] ?? false)) class="accent-blue-800">
                            Closed
                        </label>
                    </div>
                @endforeach
                @error('hours')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <button class="mt-2 justify-self-start rounded-xl bg-blue-800 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Save hours</button>
            </form>
        </section>

        <section class="mb-5 break-inside-avoid rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Facility fee</h2>
            <p class="mt-1 text-[11px] text-ink-soft">A single flat fee charged to every patient booking with any doctor at your clinic. This is separate from — and in addition to — the doctor's own session fee.</p>
            <form method="POST" action="{{ route('medical-center.settings.pricing.update') }}" class="mt-5 grid gap-4">
                @csrf @method('PATCH')
                <label class="text-[11px] font-medium text-ink">Facility fee (LKR)
                    <input type="number" step="0.01" min="0" name="facility_fee" value="{{ old('facility_fee', $clinic->facility_fee) }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('facility_fee')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <button class="justify-self-start rounded-xl bg-blue-800 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Save pricing</button>
            </form>
        </section>
    </div>
@endsection
