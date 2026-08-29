<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\StoreClinicStaffRequest;
use App\Models\ClinicStaff;
use App\Services\CurrentClinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClinicStaffController extends Controller
{
    /**
     * List this clinic's staff accounts.
     */
    public function index(CurrentClinic $currentClinic): View
    {
        return view('medical-center.staff.index', [
            'staff' => $currentClinic->model()->staff()->latest()->get(),
        ]);
    }

    /**
     * Create a new staff login for this clinic.
     */
    public function store(StoreClinicStaffRequest $request, CurrentClinic $currentClinic): RedirectResponse
    {
        $currentClinic->model()->staff()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('status', 'Staff account created.');
    }

    /**
     * Disable a staff account's access.
     */
    public function destroy(ClinicStaff $staffMember, CurrentClinic $currentClinic): RedirectResponse
    {
        abort_unless($staffMember->medical_center_id === $currentClinic->id(), 403);

        $staffMember->update(['status' => 'disabled']);

        return back()->with('status', 'Access removed.');
    }
}
