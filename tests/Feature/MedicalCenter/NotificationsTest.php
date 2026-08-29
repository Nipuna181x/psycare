<?php

namespace Tests\Feature\MedicalCenter;

use App\Http\Controllers\BookingController;
use App\Models\Appointment;
use App\Models\ClinicStaff;
use App\Models\Doctor;
use App\Models\DoctorClinicAffiliation;
use App\Models\MedicalCenter;
use App\Models\User;
use App\Notifications\MedicalCenterPortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_sees_notifications_list_grouped_and_paginated(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $clinic->notify(new MedicalCenterPortalNotification(
            type: 'new_booking',
            message: 'New booking arrived.',
            link: '/medical-center/appoinment-managment',
        ));

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.notifications.index'));

        $response->assertOk()->assertSee('New booking arrived.')->assertSee('Today');
    }

    public function test_unread_count_reflects_actual_unread_notifications(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $clinic->notify(new MedicalCenterPortalNotification(type: 'new_booking', message: 'One', link: '/medical-center/dashboard'));
        $clinic->notify(new MedicalCenterPortalNotification(type: 'new_booking', message: 'Two', link: '/medical-center/dashboard'));

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.dashboard'));

        $response->assertOk()->assertSee('2');
    }

    public function test_clinic_can_mark_one_notification_as_read(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $clinic->notify(new MedicalCenterPortalNotification(
            type: 'new_booking',
            message: 'Read me',
            link: '/medical-center/appoinment-managment',
        ));
        $notification = $clinic->notifications()->first();

        $response = $this->actingAs($clinic, 'medical_center')->post(route('medical-center.notifications.read', $notification->id));

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_clinic_can_mark_all_as_read(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $clinic->notify(new MedicalCenterPortalNotification(type: 'new_booking', message: 'One', link: '/medical-center/dashboard'));
        $clinic->notify(new MedicalCenterPortalNotification(type: 'new_booking', message: 'Two', link: '/medical-center/dashboard'));

        $response = $this->actingAs($clinic, 'medical_center')->post(route('medical-center.notifications.read-all'));

        $response->assertRedirect();
        $this->assertSame(0, $clinic->fresh()->unreadNotifications()->count());
    }

    public function test_doctor_accepting_a_request_notifies_the_clinic(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->requested()->create(['clinic_id' => $clinic->id]);

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.clinic-requests.accept', $affiliation));

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => MedicalCenter::class,
            'notifiable_id' => $clinic->id,
        ]);
        $this->assertSame('doctor_accepted', $clinic->notifications()->first()->data['type']);
    }

    public function test_doctor_declining_a_request_notifies_the_clinic(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->requested()->create(['clinic_id' => $clinic->id]);

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.clinic-requests.decline', $affiliation));

        $this->assertSame('doctor_declined', $clinic->notifications()->first()->data['type']);
    }

    public function test_new_booking_notifies_the_clinic(): void
    {
        Notification::fake();

        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create(['consultation_fee' => 4000]);
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();

        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)->post(route('booking.schedule', $doctor), [
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'mode' => 'in_person',
        ]);

        $this->actingAs($patient)->post(route('booking.details', $doctor), [
            'patient_name' => 'Jane Doe',
            'patient_age' => 29,
            'patient_gender' => 'female',
            'patient_phone' => '0771234567',
            'patient_email' => 'jane@example.com',
            'reason' => 'Feeling anxious lately',
        ]);

        $answers = collect(BookingController::ASSESSMENT_QUESTIONS)
            ->map(fn (array $question): array => [
                'key' => $question['key'],
                'instrument' => $question['instrument'],
                'question' => $question['question'],
                'score' => 0,
                'answer' => '',
                'confidence' => 'manual',
                'extracted_context' => '',
            ])
            ->all();

        $this->actingAs($patient)->post(route('booking.assessment', $doctor), [
            'answers' => $answers,
            'open_notes' => '',
        ]);

        $this->actingAs($patient)->post(route('booking.confirm', $doctor));

        Notification::assertSentTo($affiliation->clinic, MedicalCenterPortalNotification::class, fn ($notification) => $notification->type === 'new_booking');
    }

    public function test_doctor_cancelling_an_appointment_notifies_the_clinic(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        $appointment = Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'status' => 'confirmed']);

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.appointments.status', $appointment), ['status' => 'cancelled']);

        $this->assertSame('appointment_cancelled', $clinic->notifications()->first()->data['type']);
    }

    public function test_staff_login_sees_the_same_shared_notification_inbox(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $clinic->notify(new MedicalCenterPortalNotification(
            type: 'new_booking',
            message: 'Shared inbox message',
            link: '/medical-center/dashboard',
        ));
        $staff = ClinicStaff::factory()->for($clinic, 'medicalCenter')->create();

        $response = $this->actingAs($staff, 'clinic_staff')->get(route('medical-center.notifications.index'));

        $response->assertOk()->assertSee('Shared inbox message');
    }

    public function test_clinic_a_never_sees_clinic_bs_notifications(): void
    {
        $clinicA = MedicalCenter::factory()->approved()->create();
        $clinicB = MedicalCenter::factory()->approved()->create();
        $clinicB->notify(new MedicalCenterPortalNotification(
            type: 'new_booking',
            message: 'Clinic B only message',
            link: '/medical-center/dashboard',
        ));

        $response = $this->actingAs($clinicA, 'medical_center')->get(route('medical-center.notifications.index'));

        $response->assertOk()->assertDontSee('Clinic B only message');
    }
}
