<?php

namespace Tests\Feature\Doctor;

use App\Models\Doctor;
use App\Notifications\DoctorPortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_page_renders_empty_state(): void
    {
        $doctor = Doctor::factory()->create();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.notifications.index'))
            ->assertOk()
            ->assertSee("You're all caught up", false);
    }

    public function test_doctor_can_view_and_read_a_notification(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->notifyNow(new DoctorPortalNotification(
            type: 'elevated_risk',
            message: 'Elevated-risk pre-assessment flagged.',
            link: route('doctor.dashboard', absolute: false),
        ));
        $notification = $doctor->notifications()->firstOrFail();

        $this->actingAs($doctor, 'doctor')
            ->get(route('doctor.notifications.index'))
            ->assertOk()
            ->assertSee('Elevated-risk pre-assessment flagged.')
            ->assertSee('1 unread');

        $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.notifications.read', $notification->id))
            ->assertRedirect(route('doctor.dashboard', absolute: false));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_doctor_can_mark_all_notifications_as_read(): void
    {
        $doctor = Doctor::factory()->create();

        foreach (['new_booking', 'appointment_cancelled', 'new_message'] as $type) {
            $doctor->notifyNow(new DoctorPortalNotification($type, 'Notification '.$type, route('doctor.dashboard', absolute: false)));
        }

        $this->actingAs($doctor, 'doctor')
            ->post(route('doctor.notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $doctor->unreadNotifications()->count());
    }
}
