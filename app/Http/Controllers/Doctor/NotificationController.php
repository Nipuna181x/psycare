<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Auth::guard('doctor')->user()->notifications()->latest()->paginate(20);

        return view('doctor.notifications.index', [
            'notifications' => $notifications,
            'unreadNotificationCount' => Auth::guard('doctor')->user()->unreadNotifications()->count(),
            'notificationGroups' => $notifications->getCollection()->groupBy(
                fn ($notification): string => $notification->created_at->isToday() ? 'Today' : 'Earlier'
            ),
        ]);
    }

    public function read(string $notification): RedirectResponse
    {
        $notification = Auth::guard('doctor')->user()->notifications()->whereKey($notification)->firstOrFail();
        $notification->markAsRead();

        $link = $notification->data['link'] ?? route('doctor.notifications.index', absolute: false);

        return redirect(str_starts_with($link, '/doctor/') ? $link : route('doctor.notifications.index'));
    }

    public function readAll(): RedirectResponse
    {
        Auth::guard('doctor')->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
