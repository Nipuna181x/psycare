<?php

namespace App\View\Composers;

use App\Services\DoctorClinicContext;
use App\Services\DoctorCrisisQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorPortalComposer
{
    public function __construct(
        private readonly DoctorCrisisQueue $crisisQueue,
        private readonly DoctorClinicContext $clinicContext,
    ) {}

    public function compose(View $view): void
    {
        $doctor = Auth::guard('doctor')->user();

        if (! $doctor) {
            return;
        }

        $clinicId = $this->clinicContext->current($doctor);

        $view->with([
            'doctorHeaderNotifications' => $doctor->notifications()->latest()->limit(5)->get(),
            'doctorUnreadNotificationCount' => $doctor->unreadNotifications()->count(),
            'doctorEscalationCount' => $this->crisisQueue->unreviewedCount($doctor, $clinicId),
            'doctorPendingClinicRequestCount' => $doctor->affiliations()->where('status', 'requested')->count(),
            'doctorActiveClinicId' => $clinicId,
            'doctorRequiresClinicSwitcher' => $this->clinicContext->requiresSwitcher($doctor),
            'doctorActiveClinics' => $doctor->activeAffiliations()->with('clinic')->get(),
        ]);
    }
}
