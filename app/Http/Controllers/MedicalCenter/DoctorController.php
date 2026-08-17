<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\StoreDoctorRequest;
use App\Http\Requests\MedicalCenter\UpdateDoctorRequest;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DoctorController extends Controller
{
    /**
     * Display the authenticated medical center's doctors.
     */
    public function index(): View
    {
        $doctors = Auth::guard('medical_center')->user()->doctors()->latest()->paginate(15);

        return view('medical-center.doctor-managment.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create(): View
    {
        return view('medical-center.doctor-managment.create');
    }

    /**
     * Store a newly created doctor for the authenticated medical center.
     */
    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        Auth::guard('medical_center')->user()->doctors()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'username' => $request->validated('username'),
            'password' => Hash::make($request->validated('password')),
            'specialization' => $request->validated('specialization'),
            'bio' => $request->validated('bio'),
            'years_experience' => $request->validated('years_experience'),
            'consultation_fee' => $request->validated('consultation_fee'),
            'phone' => $request->validated('phone'),
            'status' => 'active',
        ]);

        return redirect()->route('medical-center.doctor-managment.index')
            ->with('status', 'Doctor account created successfully.');
    }

    /**
     * Show the form for editing the given doctor.
     */
    public function edit(Doctor $doctor): View
    {
        $this->authorizeMedicalCenterOwnsDoctor($doctor);

        return view('medical-center.doctor-managment.edit', compact('doctor'));
    }

    /**
     * Update the given doctor.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->authorizeMedicalCenterOwnsDoctor($doctor);

        $doctor->update([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'username' => $request->validated('username'),
            'specialization' => $request->validated('specialization'),
            'bio' => $request->validated('bio'),
            'years_experience' => $request->validated('years_experience'),
            'consultation_fee' => $request->validated('consultation_fee'),
            'phone' => $request->validated('phone'),
            'status' => $request->validated('status'),
            ...$request->validated('password') ? ['password' => Hash::make($request->validated('password'))] : [],
        ]);

        return redirect()->route('medical-center.doctor-managment.index')
            ->with('status', 'Doctor account updated successfully.');
    }

    /**
     * Remove the given doctor.
     */
    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->authorizeMedicalCenterOwnsDoctor($doctor);

        $doctor->delete();

        return redirect()->route('medical-center.doctor-managment.index')
            ->with('status', 'Doctor account removed.');
    }

    /**
     * Ensure the authenticated medical center owns the given doctor.
     */
    private function authorizeMedicalCenterOwnsDoctor(Doctor $doctor): void
    {
        abort_unless(
            $doctor->medical_center_id === Auth::guard('medical_center')->id(),
            403
        );
    }
}
