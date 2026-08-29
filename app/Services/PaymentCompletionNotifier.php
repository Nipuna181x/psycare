<?php

namespace App\Services;

use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Notifications\BookingConfirmed;
use App\Notifications\DoctorPortalNotification;
use App\Notifications\ElevatedRiskFlagged;
use App\Notifications\MedicalCenterPortalNotification;
use Illuminate\Support\Facades\Mail;

class PaymentCompletionNotifier
{
    public function send(Payment $payment): void
    {
        $appointment = $payment->appointment;
        $doctor = $appointment->doctor;
        $clinic = $appointment->medicalCenter;
        $patient = $appointment->user;

        $doctor->notify((new DoctorPortalNotification(
            type: 'new_booking',
            message: 'New booking from '.$appointment->patient_name.' for '.$appointment->appointment_date->format('j M Y').'.',
            link: route('doctor.appointments.show', $appointment, absolute: false),
        ))->afterCommit());

        $clinic->notify((new MedicalCenterPortalNotification(
            type: 'new_booking',
            message: 'New booking: '.$appointment->patient_name.' with Dr. '.$doctor->name.' on '.$appointment->appointment_date->format('j M Y').'.',
            link: route('medical-center.appoinment-managment.show', $appointment, absolute: false),
        ))->afterCommit());

        $patient->notify((new BookingConfirmed($appointment))->afterCommit());
        Mail::to($patient->email)->queue(new PaymentReceipt($payment));

        if ($appointment->requiresCrisisEscalation()) {
            $doctor->notify((new DoctorPortalNotification(
                type: 'elevated_risk',
                message: 'Elevated-risk pre-assessment flagged for '.$appointment->patient_name.'.',
                link: route('doctor.appointments.show', $appointment, absolute: false),
            ))->afterCommit());

            $doctor->notify((new ElevatedRiskFlagged($appointment))->onQueue('high')->afterCommit());
        }
    }
}
