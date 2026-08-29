@extends('layouts.admin')

@php
    $title = 'Notifications';
    $subtitle = 'New applications and platform administration updates';
@endphp

@section('content')
    <div class="grid gap-5">
        <section class="rounded-3xl bg-card p-5 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-semibold tracking-[0.12em] text-ink-soft uppercase">Admin inbox</p>
                    <h2 class="mt-1 font-display text-[17px] font-medium text-ink">Approval notifications</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">New medical center and completed doctor applications appear here.</p>
                </div>
                @if ($unreadNotificationCount > 0)
                    <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-border bg-white px-4 py-2.5 text-[10px] font-semibold tracking-[0.08em] text-ink uppercase transition hover:bg-secondary">Mark all as read</button>
                    </form>
                @endif
            </div>
        </section>

        @forelse ($notificationGroups as $group => $groupNotifications)
            <section class="rounded-3xl bg-card p-5 md:p-6">
                <div class="flex items-center justify-between border-b border-border pb-4">
                    <h2 class="font-display text-[15px] font-medium text-ink">{{ $group }}</h2>
                    <span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft">{{ $groupNotifications->count() }}</span>
                </div>

                <ul class="mt-4 grid gap-2.5">
                    @foreach ($groupNotifications as $notification)
                        @php
                            $notificationType = $notification->data['type'] ?? 'admin_update';
                            $isDoctorApplication = $notificationType === 'doctor_application';
                        @endphp
                        <li>
                            <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="flex w-full items-start gap-3.5 rounded-2xl border p-4 text-left transition {{ $notification->read_at ? 'border-border bg-white hover:bg-secondary/60' : 'border-amber-200 bg-amber-50/70 hover:bg-amber-50' }}">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $isDoctorApplication ? 'bg-sky-50 text-sky-700' : 'bg-violet-50 text-violet-700' }}">
                                        @if ($isDoctorApplication)
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/><path d="M19 8v4M17 10h4"/></svg>
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v18Z"/><path d="M10 7h4M10 11h4M10 15h4"/><path d="M20 13v6M17 16h6"/></svg>
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[13px] leading-relaxed {{ $notification->read_at ? 'font-medium text-ink-soft' : 'font-semibold text-ink' }}">{{ $notification->data['message'] ?? 'New administration update' }}</span>
                                        <span class="mt-1.5 flex flex-wrap items-center gap-2 text-[10px] text-ink-soft">
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            <span aria-hidden="true">·</span>
                                            <span>{{ $isDoctorApplication ? 'Doctor approval' : 'Medical center approval' }}</span>
                                        </span>
                                    </span>
                                    <span class="mt-2 flex shrink-0 items-center gap-2">
                                        @unless ($notification->read_at)<span class="h-2 w-2 rounded-full bg-red-600" aria-label="Unread"></span>@endunless
                                        <svg class="h-4 w-4 text-ink-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                    </span>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <section class="grid min-h-64 place-items-center rounded-3xl bg-card p-8 text-center">
                <div>
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-50 text-emerald-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span>
                    <h2 class="mt-4 font-display text-[16px] font-medium text-ink">No new notifications</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">New approval requests will appear here automatically.</p>
                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        <a href="{{ route('admin.medical-centers.index', ['status' => 'pending']) }}" class="rounded-xl bg-secondary px-4 py-2 text-[11px] font-semibold text-ink">Medical center applications</a>
                        <a href="{{ route('admin.doctors.index', ['status' => 'pending_approval']) }}" class="rounded-xl bg-ink px-4 py-2 text-[11px] font-semibold text-primary-foreground">Doctor applications</a>
                    </div>
                </div>
            </section>
        @endforelse

        @if ($notifications->hasPages())
            <div>{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
