<?php

namespace App\Services;

use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Auth;

class CurrentClinic
{
    /**
     * The medical_center_id in scope for this request, whether authenticated
     * as the primary medical_center login or a clinic_staff seat.
     */
    public function id(): ?int
    {
        if ($clinic = Auth::guard('medical_center')->user()) {
            return $clinic->id;
        }

        if ($staff = Auth::guard('clinic_staff')->user()) {
            return $staff->medical_center_id;
        }

        return null;
    }

    /**
     * The MedicalCenter model in scope for this request, regardless of which
     * of the two guards authenticated it.
     */
    public function model(): ?MedicalCenter
    {
        if ($clinic = Auth::guard('medical_center')->user()) {
            return $clinic;
        }

        if ($staff = Auth::guard('clinic_staff')->user()) {
            return $staff->medicalCenter;
        }

        return null;
    }

    /**
     * Display name for the currently authenticated actor — the clinic's own
     * name for a primary login, or the staff member's own name for a seat.
     */
    public function actorLabel(): string
    {
        if ($staff = Auth::guard('clinic_staff')->user()) {
            return $staff->name;
        }

        return $this->model()?->name ?? '';
    }
}
