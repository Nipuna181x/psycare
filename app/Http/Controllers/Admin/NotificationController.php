<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $admin = Auth::guard('admin')->user();
        $notifications = $admin->notifications()->latest()->paginate(20);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadNotificationCount' => $admin->unreadNotifications()->count(),
            'notificationGroups' => $notifications->getCollection()->groupBy(
                fn ($notification): string => $notification->created_at->isToday() ? 'Today' : 'Earlier'
            ),
        ]);
    }

    public function read(string $notification): RedirectResponse
    {
        $notification = Auth::guard('admin')->user()->notifications()->whereKey($notification)->firstOrFail();
        $notification->markAsRead();

        $link = $notification->data['link'] ?? route('admin.notifications.index', absolute: false);

        return redirect(str_starts_with($link, '/admin/') ? $link : route('admin.notifications.index'));
    }

    public function readAll(): RedirectResponse
    {
        Auth::guard('admin')->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
