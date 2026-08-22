<?php

use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MedicalCenterController as AdminMedicalCenterController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Doctor\AuthenticatedSessionController as DoctorAuthenticatedSessionController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\DoctorController as PublicDoctorController;
use App\Http\Controllers\MedicalCenter\AppointmentController as MedicalCenterAppointmentController;
use App\Http\Controllers\MedicalCenter\AuthenticatedSessionController as MedicalCenterAuthenticatedSessionController;
use App\Http\Controllers\MedicalCenter\DashboardController as MedicalCenterDashboardController;
use App\Http\Controllers\MedicalCenter\DoctorController;
use App\Http\Controllers\MedicalCenter\RegisteredMedicalCenterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('doctors', [PublicDoctorController::class, 'index'])->name('doctors.index');
Route::get('doctors/{doctor}', [PublicDoctorController::class, 'show'])->name('doctors.show');

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

Route::middleware('auth')->group(function () {
    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');

    Route::prefix('booking/{doctor}')->name('booking.')->group(function () {
        Route::get('schedule', [BookingController::class, 'schedule'])->name('schedule');
        Route::post('schedule', [BookingController::class, 'storeSchedule']);
        Route::get('slots', [BookingController::class, 'slots'])->name('slots');
        Route::get('details', [BookingController::class, 'details'])->name('details');
        Route::post('details', [BookingController::class, 'storeDetails']);
        Route::get('assessment', [BookingController::class, 'assessment'])->name('assessment');
        Route::post('assessment', [BookingController::class, 'storeAssessment']);
        Route::post('assessment/interpret', [BookingController::class, 'interpretAnswer'])->middleware('throttle:30,1')->name('assessment.interpret');
        Route::get('review', [BookingController::class, 'review'])->name('review');
        Route::post('review', [BookingController::class, 'confirm'])->name('confirm');
    });

    Route::get('booking/confirmed/{appointment}', [BookingController::class, 'confirmed'])->name('booking.confirmed');
});

// Super Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

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

        Route::get('dashboard', [MedicalCenterDashboardController::class, 'index'])->name('dashboard');

        Route::resource('doctor-managment', DoctorController::class)
            ->parameters(['doctor-managment' => 'doctor'])
            ->except(['show']);

        Route::get('appoinment-managment', [MedicalCenterAppointmentController::class, 'index'])->name('appoinment-managment.index');
        Route::get('appoinment-managment/{appointment}', [MedicalCenterAppointmentController::class, 'show'])->name('appoinment-managment.show');
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

        Route::get('dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');

        Route::get('appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{appointment}', [DoctorAppointmentController::class, 'show'])->name('appointments.show');
        Route::patch('appointments/{appointment}/status', [DoctorAppointmentController::class, 'updateStatus'])->name('appointments.status');
    });
});
