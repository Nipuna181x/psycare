<?php

namespace App\View\Composers;

use App\Services\DoctorCrisisQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorPortalComposer
{
    public function __construct(private readonly DoctorCrisisQueue $crisisQueue) {}

    public function compose(View $view): void
    {
        $doctor = Auth::guard('doctor')->user();

        if (! $doctor) {
            return;
        }

        $view->with([
            'doctorHeaderNotifications' => $doctor->notifications()->latest()->limit(5)->get(),
            'doctorUnreadNotificationCount' => $doctor->unreadNotifications()->count(),
            'doctorEscalationCount' => $this->crisisQueue->unreviewedCount($doctor),
        ]);
    }
}
