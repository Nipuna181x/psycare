@extends('layouts.admin')

@php
    $title = 'Settings';
    $subtitle = 'Manage your super-admin account and security';
@endphp

@section('content')
    <div class="grid gap-5 lg:grid-cols-2">
        <x-dashboard.panel title="Profile information" subtitle="Used to identify your administrator account">
            <form method="POST" action="{{ route('admin.settings.profile.update') }}" class="space-y-5">
                @csrf @method('PATCH')
                <label class="block"><span class="text-[11px] font-semibold text-ink">Name</span><input name="name" value="{{ old('name', $admin->name) }}" required class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10">@error('name')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-[11px] font-semibold text-ink">Email address</span><input type="email" name="email" value="{{ old('email', $admin->email) }}" required class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10">@error('email')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
                <button class="rounded-2xl bg-ink px-5 py-3 text-[12px] font-semibold text-white transition hover:opacity-90">Save profile</button>
            </form>
        </x-dashboard.panel>

        <x-dashboard.panel title="Change password" subtitle="Use a strong, unique password for this account">
            <form method="POST" action="{{ route('admin.settings.password.update') }}" class="space-y-5">
                @csrf @method('PATCH')
                <label class="block"><span class="text-[11px] font-semibold text-ink">Current password</span><input type="password" name="current_password" autocomplete="current-password" required class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10">@error('current_password')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-[11px] font-semibold text-ink">New password</span><input type="password" name="password" autocomplete="new-password" required class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10">@error('password')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
                <label class="block"><span class="text-[11px] font-semibold text-ink">Confirm new password</span><input type="password" name="password_confirmation" autocomplete="new-password" required class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none focus:border-ink/50 focus:ring-2 focus:ring-ink/10"></label>
                <button class="rounded-2xl bg-ink px-5 py-3 text-[12px] font-semibold text-white transition hover:opacity-90">Update password</button>
            </form>
        </x-dashboard.panel>

        <x-dashboard.panel title="System tools" subtitle="Administrative diagnostics and account controls" class="lg:col-span-2">
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.mail-check.index') }}" class="flex items-center justify-between rounded-2xl border border-border p-4 transition hover:border-ink/30 hover:bg-ink/5"><div><p class="text-[12px] font-semibold text-ink">SMTP delivery check</p><p class="mt-1 text-[11px] text-ink-soft">Verify outbound email configuration.</p></div><span class="text-ink">→</span></a>
                <form method="POST" action="{{ route('admin.logout') }}" class="flex">@csrf<button class="flex w-full items-center justify-between rounded-2xl border border-border p-4 text-left transition hover:border-red-200 hover:bg-red-50"><span><span class="block text-[12px] font-semibold text-ink">Sign out</span><span class="mt-1 block text-[11px] text-ink-soft">End this administrator session.</span></span><span class="text-red-600">→</span></button></form>
            </div>
        </x-dashboard.panel>
    </div>
@endsection
