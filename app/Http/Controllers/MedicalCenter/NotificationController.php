<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Services\CurrentClinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(CurrentClinic $currentClinic): View
    {
        $clinic = $currentClinic->model();
        $notifications = $clinic->notifications()->latest()->paginate(20);

        return view('medical-center.notifications.index', [
            'notifications' => $notifications,
            'unreadNotificationCount' => $clinic->unreadNotifications()->count(),
            'notificationGroups' => $notifications->getCollection()->groupBy(
                fn ($notification): string => $notification->created_at->isToday() ? 'Today' : 'Earlier'
            ),
        ]);
    }

    public function read(string $notification, CurrentClinic $currentClinic): RedirectResponse
    {
        $clinic = $currentClinic->model();
        $notification = $clinic->notifications()->whereKey($notification)->firstOrFail();
        $notification->markAsRead();

        $link = $notification->data['link'] ?? route('medical-center.notifications.index', absolute: false);

        return redirect(str_starts_with($link, '/medical-center/') ? $link : route('medical-center.notifications.index'));
    }

    public function readAll(CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
