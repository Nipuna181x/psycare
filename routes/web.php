<?php

use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\MedicalCenterController as AdminMedicalCenterController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Doctor\AuthenticatedSessionController as DoctorAuthenticatedSessionController;
use App\Http\Controllers\MedicalCenter\AuthenticatedSessionController as MedicalCenterAuthenticatedSessionController;
use App\Http\Controllers\MedicalCenter\DoctorController;
use App\Http\Controllers\MedicalCenter\RegisteredMedicalCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Patient (web guard)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Super Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::view('dashboard', 'admin.dashboard')->name('dashboard');

        Route::get('user-managment', [AdminMedicalCenterController::class, 'index'])->name('user-managment.index');
        Route::patch('user-managment/medical-centers/{medicalCenter}/approve', [AdminMedicalCenterController::class, 'approve'])->name('user-managment.medical-centers.approve');
        Route::patch('user-managment/medical-centers/{medicalCenter}/reject', [AdminMedicalCenterController::class, 'reject'])->name('user-managment.medical-centers.reject');
    });
});

// Medical Center (clinic/hospital)
Route::prefix('medical-center')->name('medical-center.')->group(function () {
    Route::middleware('guest:medical_center')->group(function () {
        Route::get('register', [RegisteredMedicalCenterController::class, 'create'])->name('register');
        Route::post('register', [RegisteredMedicalCenterController::class, 'store']);

        Route::get('login', [MedicalCenterAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [MedicalCenterAuthenticatedSessionController::class, 'store']);
    });

    Route::middleware(['auth:medical_center', 'medical_center.approved'])->group(function () {
        Route::post('logout', [MedicalCenterAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::view('dashboard', 'medical-center.dashboard')->name('dashboard');

        Route::resource('doctor-managment', DoctorController::class)
            ->parameters(['doctor-managment' => 'doctor'])
            ->except(['show']);
    });
});

// Doctor
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::middleware('guest:doctor')->group(function () {
        Route::get('login', [DoctorAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [DoctorAuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth:doctor')->group(function () {
        Route::post('logout', [DoctorAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::view('dashboard', 'doctor.dashboard')->name('dashboard');
    });
});
