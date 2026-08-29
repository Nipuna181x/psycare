@extends('layouts.medical-center')

@php
    $title = 'Clinic Staff';
    $subtitle = 'Manage additional login accounts for your clinic';
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-[1fr_1.4fr]">
        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <h2 class="font-display text-[16px] font-medium text-ink">Add staff account</h2>
            <p class="mt-1 text-[11px] text-ink-soft">Staff accounts have the same access as the primary clinic login, except managing other staff accounts.</p>
            <form method="POST" action="{{ route('medical-center.staff.store') }}" class="mt-5 grid gap-4">
                @csrf
                <label class="text-[11px] font-medium text-ink">Name
                    <input type="text" name="name" value="{{ old('name') }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('name')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <label class="text-[11px] font-medium text-ink">Email
                    <input type="email" name="email" value="{{ old('email') }}" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('email')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <label class="text-[11px] font-medium text-ink">Password
                    <input type="password" name="password" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                @error('password')<p class="text-[11px] text-red-700">{{ $message }}</p>@enderror
                <label class="text-[11px] font-medium text-ink">Confirm password
                    <input type="password" name="password_confirmation" required class="mt-1.5 w-full rounded-xl border border-border bg-white px-3.5 py-2.5 text-[12px] text-ink outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                </label>
                <button class="justify-self-start rounded-xl bg-blue-800 px-5 py-3 text-[11px] font-semibold tracking-[0.1em] text-white uppercase hover:bg-blue-900">Create staff account</button>
            </form>
        </section>

        <x-dashboard.panel title="Existing staff">
            <ul class="divide-y divide-border">
                @forelse ($staff as $member)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3.5 first:pt-0 last:pb-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-blue-100 text-[12px] font-semibold text-blue-800">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-[13px] font-medium text-ink">{{ $member->name }}</p>
                                <p class="mt-0.5 truncate text-[11px] text-ink-soft">{{ $member->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-dashboard.badge :status="$member->status" />
                            @if ($member->status === 'active')
                                <form method="POST" action="{{ route('medical-center.staff.destroy', $member) }}" onsubmit="return confirm('Remove access for {{ $member->name }}?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-red-200 bg-white px-3 py-1.5 text-[10px] font-semibold tracking-[0.06em] text-red-700 uppercase hover:bg-red-50">Remove access</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="py-3.5 text-[12px] text-ink-soft">No staff accounts yet.</li>
                @endforelse
            </ul>
        </x-dashboard.panel>
    </div>
@endsection
