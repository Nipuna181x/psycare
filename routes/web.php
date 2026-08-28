<?php

use App\Http\Controllers\Admin\AuthenticatedSessionController as AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DoctorApprovalController as AdminDoctorApprovalController;
use App\Http\Controllers\Admin\MedicalCenterController as AdminMedicalCenterController;
use App\Http\Controllers\AiCompanionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Doctor\AuthenticatedSessionController as DoctorAuthenticatedSessionController;
use App\Http\Controllers\Doctor\ClinicContextController as DoctorClinicContextController;
use App\Http\Controllers\Doctor\ClinicRequestController as DoctorClinicRequestController;
use App\Http\Controllers\Doctor\CrisisQueueController as DoctorCrisisQueueController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\MedicationController as DoctorMedicationController;
use App\Http\Controllers\Doctor\NotificationController as DoctorNotificationController;
use App\Http\Controllers\Doctor\OnboardingController as DoctorOnboardingController;
use App\Http\Controllers\Doctor\PatientController as DoctorPatientController;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfileController;
use App\Http\Controllers\Doctor\RegisteredDoctorController;
use App\Http\Controllers\Doctor\StatusController as DoctorStatusController;
use App\Http\Controllers\Doctor\TherapyRoomController as DoctorTherapyRoomController;
use App\Http\Controllers\DoctorController as PublicDoctorController;
use App\Http\Controllers\MedicalCenter\AffiliationController as MedicalCenterAffiliationController;
use App\Http\Controllers\MedicalCenter\AppointmentController as MedicalCenterAppointmentController;
use App\Http\Controllers\MedicalCenter\AuthenticatedSessionController as MedicalCenterAuthenticatedSessionController;
use App\Http\Controllers\MedicalCenter\DashboardController as MedicalCenterDashboardController;
use App\Http\Controllers\MedicalCenter\DoctorSearchController as MedicalCenterDoctorSearchController;
use App\Http\Controllers\MedicalCenter\RegisteredMedicalCenterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientConversationController;
use App\Http\Controllers\PatientNlpClassificationReportController;
use App\Http\Controllers\TherapyRoomController;
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
    Route::get('ai-companion', [AiCompanionController::class, 'show'])->name('ai-companion.show');
    Route::post('ai-companion/start', [AiCompanionController::class, 'start'])
        ->middleware('throttle:20,1')
        ->name('ai-companion.start');
    Route::post('ai-companion/respond', [AiCompanionController::class, 'respond'])
        ->middleware('throttle:20,1')
        ->name('ai-companion.respond');
    Route::post('ai-companion/finish', [AiCompanionController::class, 'finish'])
        ->middleware('throttle:10,1')
        ->name('ai-companion.finish');

    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');

    Route::prefix('booking/{doctor}')->name('booking.')->group(function () {
        Route::get('clinic', [BookingController::class, 'clinic'])->name('clinic');
        Route::post('clinic', [BookingController::class, 'storeClinic']);
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

    Route::prefix('therapy-rooms')->name('therapy-rooms.')->group(function () {
        Route::get('/', [TherapyRoomController::class, 'index'])->name('index');
        Route::get('{therapyRoom}', [TherapyRoomController::class, 'show'])->name('show');
        Route::get('{therapyRoom}/session', [TherapyRoomController::class, 'join'])->name('session');
        Route::post('{therapyRoom}/signal', [TherapyRoomController::class, 'signal'])->name('signal');
    });

    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
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

        Route::get('patients/{patient}/nlp-report', [PatientNlpClassificationReportController::class, 'show'])->name('patients.nlp-report.show');
        Route::post('patients/{patient}/nlp-report/sync', [PatientNlpClassificationReportController::class, 'sync'])->name('patients.nlp-report.sync');
        Route::get('patients/{patient}/conversations', [PatientConversationController::class, 'index'])->name('patients.conversations.index');
        Route::get('patients/{patient}/conversations/{session}', [PatientConversationController::class, 'show'])->name('patients.conversations.show');

        Route::get('user-managment', [AdminMedicalCenterController::class, 'index'])->name('user-managment.index');
        Route::patch('user-managment/medical-centers/{medicalCenter}/approve', [AdminMedicalCenterController::class, 'approve'])->name('user-managment.medical-centers.approve');
        Route::patch('user-managment/medical-centers/{medicalCenter}/reject', [AdminMedicalCenterController::class, 'reject'])->name('user-managment.medical-centers.reject');

        Route::get('doctor-approvals', [AdminDoctorApprovalController::class, 'index'])->name('doctor-approvals.index');
        Route::patch('doctor-approvals/{doctor}/approve', [AdminDoctorApprovalController::class, 'approve'])->name('doctor-approvals.approve');
        Route::patch('doctor-approvals/{doctor}/reject', [AdminDoctorApprovalController::class, 'reject'])->name('doctor-approvals.reject');
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

        Route::get('find-doctors', [MedicalCenterDoctorSearchController::class, 'index'])->name('find-doctors.index');
        Route::post('find-doctors/{doctor}/request', [MedicalCenterDoctorSearchController::class, 'sendRequest'])->name('find-doctors.request');

        Route::get('affiliations', [MedicalCenterAffiliationController::class, 'index'])->name('affiliations.index');

        Route::get('appoinment-managment', [MedicalCenterAppointmentController::class, 'index'])->name('appoinment-managment.index');
        Route::get('appoinment-managment/{appointment}', [MedicalCenterAppointmentController::class, 'show'])->name('appoinment-managment.show');
    });
});

// Doctor
Route::prefix('doctor')->name('doctor.')->group(function () {
    Route::middleware('guest:doctor')->group(function () {
        Route::get('register', [RegisteredDoctorController::class, 'create'])->name('register');
        Route::post('register', [RegisteredDoctorController::class, 'store']);

        Route::get('login', [DoctorAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [DoctorAuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth:doctor')->group(function () {
        Route::post('logout', [DoctorAuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('onboarding', [DoctorOnboardingController::class, 'edit'])->name('onboarding.edit');
        Route::patch('onboarding', [DoctorOnboardingController::class, 'update'])->name('onboarding.update');

        Route::get('pending', [DoctorStatusController::class, 'pending'])->name('pending');
        Route::get('blocked', [DoctorStatusController::class, 'blocked'])->name('blocked');
    });

    Route::middleware(['auth:doctor', 'doctor.onboarding'])->group(function () {
        Route::get('dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');

        Route::get('crisis-queue', [DoctorCrisisQueueController::class, 'index'])->name('crisis-queue.index');
        Route::patch('crisis-queue/{appointment}/acknowledge', [DoctorCrisisQueueController::class, 'acknowledge'])->name('crisis-queue.acknowledge');

        Route::get('clinic-requests', [DoctorClinicRequestController::class, 'index'])->name('clinic-requests.index');
        Route::patch('clinic-requests/{affiliation}/accept', [DoctorClinicRequestController::class, 'accept'])->name('clinic-requests.accept');
        Route::patch('clinic-requests/{affiliation}/decline', [DoctorClinicRequestController::class, 'decline'])->name('clinic-requests.decline');

        Route::post('clinic-context', [DoctorClinicContextController::class, 'update'])->name('clinic-context.update');

        Route::get('notifications', [DoctorNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [DoctorNotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [DoctorNotificationController::class, 'read'])->name('notifications.read');

        Route::get('profile', [DoctorProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile/information', [DoctorProfileController::class, 'updateProfile'])->name('profile.information.update');
        Route::patch('profile/contact', [DoctorProfileController::class, 'updateContact'])->name('profile.contact.update');
        Route::patch('profile/password', [DoctorProfileController::class, 'updatePassword'])->name('profile.password.update');

        Route::get('appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/{appointment}', [DoctorAppointmentController::class, 'show'])->name('appointments.show');
        Route::patch('appointments/{appointment}/status', [DoctorAppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::post('appointments/{appointment}/medications', [DoctorMedicationController::class, 'store'])->name('appointments.medications.store');

        Route::get('patients', [DoctorPatientController::class, 'index'])->name('patients.index');
        Route::get('patients/{patient}', [DoctorPatientController::class, 'show'])->name('patients.show');
        Route::post('patients/{patient}/reports/generate', [DoctorPatientController::class, 'generateReports'])->name('patients.reports.generate');
        Route::get('patients/{patient}/reports/history/download', [DoctorPatientController::class, 'downloadHistory'])->name('patients.reports.history-download');
        Route::get('patients/{patient}/reports/{report}/download', [DoctorPatientController::class, 'downloadReport'])->name('patients.reports.download');
        Route::get('patients/{patient}/nlp-report', [PatientNlpClassificationReportController::class, 'show'])->name('patients.nlp-report.show');
        Route::post('patients/{patient}/nlp-report/sync', [PatientNlpClassificationReportController::class, 'sync'])->name('patients.nlp-report.sync');
        Route::get('patients/{patient}/conversations', [PatientConversationController::class, 'index'])->name('patients.conversations.index');
        Route::get('patients/{patient}/conversations/{session}', [PatientConversationController::class, 'show'])->name('patients.conversations.show');

        Route::prefix('therapy-rooms')->name('therapy-rooms.')->group(function () {
            Route::get('/', [DoctorTherapyRoomController::class, 'index'])->name('index');
            Route::get('create', [DoctorTherapyRoomController::class, 'create'])->name('create');
            Route::post('/', [DoctorTherapyRoomController::class, 'store'])->name('store');
            Route::get('{therapyRoom}', [DoctorTherapyRoomController::class, 'show'])->name('show');
            Route::get('{therapyRoom}/edit', [DoctorTherapyRoomController::class, 'edit'])->name('edit');
            Route::patch('{therapyRoom}', [DoctorTherapyRoomController::class, 'update'])->name('update');
            Route::post('{therapyRoom}/participants', [DoctorTherapyRoomController::class, 'addParticipant'])->name('participants.store');
            Route::delete('{therapyRoom}/participants/{participant}', [DoctorTherapyRoomController::class, 'removeParticipant'])->name('participants.destroy');
            Route::post('{therapyRoom}/participants/{participant}/kick', [DoctorTherapyRoomController::class, 'kickParticipant'])->name('participants.kick');
            Route::post('{therapyRoom}/start', [DoctorTherapyRoomController::class, 'start'])->name('start');
            Route::post('{therapyRoom}/end', [DoctorTherapyRoomController::class, 'end'])->name('end');
            Route::get('{therapyRoom}/session', [DoctorTherapyRoomController::class, 'session'])->name('session');
            Route::get('{therapyRoom}/roster', [DoctorTherapyRoomController::class, 'roster'])->name('roster');
            Route::post('{therapyRoom}/signal', [DoctorTherapyRoomController::class, 'signal'])->name('signal');
        });
    });
});
