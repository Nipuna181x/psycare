<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Notifications\ClinicWorkRequestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClinicWorkRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_is_notified_by_email_when_a_clinic_sends_a_work_request(): void
    {
        Notification::fake();

        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();

        $response = $this->actingAs($clinic, 'medical_center')
            ->post(route('medical-center.doctors.request', $doctor));

        $response->assertRedirect();

        Notification::assertSentTo(
            $doctor,
            ClinicWorkRequestReceived::class,
            fn (ClinicWorkRequestReceived $notification) => $notification->affiliation->clinic_id === $clinic->id
                && $notification->affiliation->doctor_id === $doctor->id
        );
    }
}
