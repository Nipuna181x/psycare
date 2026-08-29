@extends('layouts.admin')

@php
    $title = 'SMTP Check';
    $subtitle = 'Verify the platform mail server with a real delivery test';
@endphp

@section('content')
    <div class="grid gap-5 xl:grid-cols-[0.85fr_1.15fr] xl:items-start">
        <x-dashboard.panel title="Mail configuration" subtitle="Current runtime values; credentials are never displayed">
            <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                <div class="rounded-2xl bg-secondary/60 p-4"><dt class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Default mailer</dt><dd class="mt-1.5 text-[12px] font-semibold text-ink">{{ strtoupper($mailer) }}</dd></div>
                <div class="rounded-2xl bg-secondary/60 p-4"><dt class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">SMTP endpoint</dt><dd class="mt-1.5 text-[12px] font-semibold text-ink">{{ $host ?: 'Not configured' }}{{ $port ? ':'.$port : '' }}</dd></div>
                <div class="rounded-2xl bg-secondary/60 p-4"><dt class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Connection security</dt><dd class="mt-1.5 text-[12px] font-semibold text-ink">{{ $security }}</dd></div>
                <div class="rounded-2xl bg-secondary/60 p-4"><dt class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Sender</dt><dd class="mt-1.5 break-all text-[12px] font-semibold text-ink">{{ $fromAddress }}</dd></div>
                <div class="rounded-2xl bg-secondary/60 p-4"><dt class="text-[9px] font-semibold tracking-[0.08em] text-ink-soft uppercase">Authentication</dt><dd class="mt-1.5 text-[12px] font-semibold {{ $authenticationConfigured ? 'text-emerald-700' : 'text-amber-700' }}">{{ $authenticationConfigured ? 'Credentials configured' : 'No credentials configured' }}</dd></div>
            </dl>
        </x-dashboard.panel>

        <x-dashboard.panel title="Send a test email" subtitle="This runs synchronously and reports the SMTP delivery result immediately">
            <div class="rounded-2xl border {{ $smtpConfigured ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }} px-4 py-3 text-[11px] leading-relaxed">
                {{ $smtpConfigured ? 'SMTP appears configured. Send a test to verify authentication and delivery.' : 'The active mailer or sender configuration is incomplete. Update your environment and clear the configuration cache before testing.' }}
            </div>

            @if (session('smtp_success'))
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[12px] font-medium text-emerald-800">{{ session('smtp_success') }}</div>
            @endif

            @if (session('smtp_error'))
                <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[12px] font-medium text-red-800">{{ session('smtp_error') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.mail-check.send') }}" class="mt-5 grid gap-4">
                @csrf
                <label class="text-[11px] font-medium text-ink">
                    Recipient email
                    <input type="email" name="email" value="{{ old('email', auth('admin')->user()->email) }}" required autocomplete="email" placeholder="admin@example.com" class="mt-2 w-full rounded-2xl border border-border bg-white px-4 py-3 text-[13px] text-ink outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                    @error('email')<span class="mt-1.5 block text-[11px] text-red-700">{{ $message }}</span>@enderror
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-800 px-5 py-3.5 text-[10px] font-semibold tracking-[0.1em] text-white uppercase transition-colors hover:bg-blue-900 sm:w-auto sm:justify-self-start">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    Send SMTP test
                </button>
            </form>

            <p class="mt-5 border-t border-border pt-4 text-[10px] leading-relaxed text-ink-soft">If environment values were changed recently, run <code class="rounded bg-secondary px-1.5 py-0.5 text-ink">php artisan config:clear</code> before testing. Production should also have a queue worker running for transactional emails.</p>
        </x-dashboard.panel>
    </div>
@endsection
