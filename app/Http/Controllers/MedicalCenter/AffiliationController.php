<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AffiliationController extends Controller
{
    /**
     * List work requests this clinic has sent, and their current status.
     */
    public function index(): View
    {
        $affiliations = Auth::guard('medical_center')->user()
            ->affiliations()
            ->with('doctor')
            ->latest()
            ->paginate(15);

        return view('medical-center.affiliations.index', compact('affiliations'));
    }
}
