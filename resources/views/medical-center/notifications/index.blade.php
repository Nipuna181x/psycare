@extends('layouts.medical-center')

@php
    $title = 'Notifications';
    $subtitle = 'Clinic and doctor updates, newest first';
@endphp

@section('content')
    <div class="grid gap-5">
        <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[10px] font-semibold tracking-[0.12em] text-blue-800 uppercase">Inbox</p>
                    <h2 class="mt-1 font-display text-[17px] font-medium text-ink">All notifications</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">Bookings, cancellations, and doctor responses appear here.</p>
                </div>
                @if ($unreadNotificationCount > 0)
                    <form method="POST" action="{{ route('medical-center.notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-[10px] font-semibold tracking-[0.08em] text-blue-800 uppercase hover:bg-blue-50">Mark all as read</button>
                    </form>
                @endif
            </div>
        </section>

        @forelse ($notificationGroups as $group => $groupNotifications)
            <section class="rounded-3xl bg-card p-5 shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)] md:p-6">
                <div class="flex items-center justify-between border-b border-border pb-4">
                    <h2 class="font-display text-[15px] font-medium text-ink">{{ $group }}</h2>
                    <span class="rounded-full bg-secondary px-3 py-1 text-[10px] font-semibold text-ink-soft">{{ $groupNotifications->count() }}</span>
                </div>
                <ul class="mt-4 grid gap-2.5">
                    @foreach ($groupNotifications as $notification)
                        @php
                            $notificationType = $notification->data['type'] ?? 'new_booking';
                            $iconTone = match ($notificationType) {
                                'appointment_cancelled' => 'bg-slate-100 text-slate-600',
                                'doctor_declined' => 'bg-red-50 text-red-700',
                                'doctor_accepted' => 'bg-emerald-50 text-emerald-700',
                                default => 'bg-blue-50 text-blue-800',
                            };
                        @endphp
                        <li>
                            <form method="POST" action="{{ route('medical-center.notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="flex w-full items-start gap-3.5 rounded-2xl border border-border p-4 text-left transition-colors hover:border-blue-200 hover:bg-blue-50/40 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/70' }}">
                                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $iconTone }}">
                                        @if ($notificationType === 'doctor_accepted')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg>
                                        @elseif ($notificationType === 'doctor_declined')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                                        @elseif ($notificationType === 'appointment_cancelled')
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg>
                                        @else
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M12 14v4M10 16h4"/></svg>
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[13px] leading-relaxed {{ $notification->read_at ? 'font-medium text-ink-soft' : 'font-semibold text-ink' }}">{{ $notification->data['message'] ?? 'Clinic portal update' }}</span>
                                        <span class="mt-1 block text-[10px] text-ink-soft">{{ $notification->created_at->diffForHumans() }}</span>
                                    </span>
                                    @unless ($notification->read_at)<span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-blue-800"></span>@endunless
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <section class="grid min-h-64 place-items-center rounded-3xl bg-card p-8 text-center shadow-[0_12px_36px_-28px_rgba(15,23,42,0.28)]">
                <div>
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-50 text-emerald-700"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 4 4L19 6"/></svg></span>
                    <h2 class="mt-4 font-display text-[16px] font-medium text-ink">You're all caught up</h2>
                    <p class="mt-1 text-[12px] text-ink-soft">New booking and doctor updates will appear here.</p>
                </div>
            </section>
        @endforelse

        @if ($notifications->hasPages())
            <div>{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection
