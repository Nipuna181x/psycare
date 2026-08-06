<?php

namespace App\Http\Controllers\MedicalCenter;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCenter\RegisterMedicalCenterRequest;
use App\Models\MedicalCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredMedicalCenterController extends Controller
{
    /**
     * Display the medical center registration view.
     */
    public function create(): View
    {
        return view('medical-center.auth.register');
    }

    /**
     * Handle an incoming medical center registration request.
     */
    public function store(RegisterMedicalCenterRequest $request): RedirectResponse
    {
        MedicalCenter::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'registration_number' => $request->validated('registration_number'),
            'password' => Hash::make($request->validated('password')),
            'status' => 'pending',
        ]);

        return redirect()->route('medical-center.login')
            ->with('status', 'Registration submitted. Your account will be reviewed by an admin before you can log in.');
    }
}
