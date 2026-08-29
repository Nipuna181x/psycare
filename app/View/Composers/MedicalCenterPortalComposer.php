<?php

namespace App\View\Composers;

use App\Services\CurrentClinic;
use Illuminate\View\View;

class MedicalCenterPortalComposer
{
    public function __construct(private readonly CurrentClinic $currentClinic) {}

    public function compose(View $view): void
    {
        $clinic = $this->currentClinic->model();

        if (! $clinic) {
            return;
        }

        $view->with([
            'clinicHeaderNotifications' => $clinic->notifications()->latest()->limit(5)->get(),
            'clinicUnreadNotificationCount' => $clinic->unreadNotifications()->count(),
            'clinicPendingRequestCount' => $clinic->affiliations()->where('status', 'requested')->count(),
            'currentClinic' => $clinic,
            'currentActorLabel' => $this->currentClinic->actorLabel(),
        ]);
    }
}
