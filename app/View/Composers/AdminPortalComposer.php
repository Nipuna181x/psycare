<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminPortalComposer
{
    public function compose(View $view): void
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return;
        }

        $view->with([
            'adminHeaderNotifications' => $admin->notifications()->latest()->limit(5)->get(),
            'adminUnreadNotificationCount' => $admin->unreadNotifications()->count(),
        ]);
    }
}
