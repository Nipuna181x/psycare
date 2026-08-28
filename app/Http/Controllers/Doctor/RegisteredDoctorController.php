<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\RegisterDoctorRequest;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredDoctorController extends Controller
{
    /**
     * Display the doctor registration view.
     */
    public function create(): View
    {
        return view('doctor.auth.register');
    }

    /**
     * Handle an incoming doctor registration request.
     */
    public function store(RegisterDoctorRequest $request): RedirectResponse
    {
        $doctor = Doctor::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'license_number' => $request->validated('license_number'),
            'phone' => $request->validated('phone'),
            'status' => 'pending_approval',
            'onboarding_step' => 'basic_info_done',
        ]);

        Auth::guard('doctor')->login($doctor);

        return redirect()->route('doctor.dashboard');
    }
}
