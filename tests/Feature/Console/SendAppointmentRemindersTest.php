<?php

namespace Tests\Feature\Console;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\User;
use App\Notifications\AppointmentReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendAppointmentRemindersTest extends TestCase
{
    use RefreshDatabase;

    private function appointmentAt(Carbon $instant, array $overrides = []): Appointment
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $clinic = MedicalCenter::factory()->approved()->create();

        return Appointment::factory()->create(array_merge([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'medical_center_id' => $clinic->id,
            'appointment_date' => $instant->toDateString(),
            'appointment_time' => $instant->format('H:i'),
            'status' => 'confirmed',
        ], $overrides));
    }

    public function test_sends_a_24h_reminder_for_an_appointment_in_that_window(): void
    {
        Notification::fake();

        $appointment = $this->appointmentAt(now()->addHours(24));

        $this->artisan('appointments:send-reminders')->assertExitCode(0);

        Notification::assertSentTo(
            $appointment->user,
            AppointmentReminder::class,
            fn (AppointmentReminder $notification) => $notification->window === '24h'
        );
        $this->assertNotNull($appointment->fresh()->reminder_24h_sent_at);
    }

    public function test_sends_a_1h_reminder_for_an_appointment_in_that_window(): void
    {
        Notification::fake();

        $appointment = $this->appointmentAt(now()->addHour());

        $this->artisan('appointments:send-reminders')->assertExitCode(0);

        Notification::assertSentTo(
            $appointment->user,
            AppointmentReminder::class,
            fn (AppointmentReminder $notification) => $notification->window === '1h'
        );
        $this->assertNotNull($appointment->fresh()->reminder_1h_sent_at);
    }

    public function test_does_not_send_a_duplicate_reminder_on_a_second_run(): void
    {
        Notification::fake();

        $appointment = $this->appointmentAt(now()->addHours(24));

        $this->artisan('appointments:send-reminders');
        $this->artisan('appointments:send-reminders');

        Notification::assertSentTimes(AppointmentReminder::class, 1);
    }

    public function test_does_not_resend_when_reminder_already_marked_sent(): void
    {
        Notification::fake();

        $appointment = $this->appointmentAt(now()->addHours(24), [
            'reminder_24h_sent_at' => now()->subMinute(),
        ]);

        $this->artisan('appointments:send-reminders');

        Notification::assertNotSentTo($appointment->user, AppointmentReminder::class);
    }

    public function test_does_not_send_a_reminder_outside_any_window(): void
    {
        Notification::fake();

        $appointment = $this->appointmentAt(now()->addDays(3));

        $this->artisan('appointments:send-reminders');

        Notification::assertNothingSent();
    }

    public function test_does_not_send_a_reminder_for_a_cancelled_appointment(): void
    {
        Notification::fake();

        $appointment = $this->appointmentAt(now()->addHours(24), ['status' => 'cancelled']);

        $this->artisan('appointments:send-reminders');

        Notification::assertNothingSent();
    }

    public function test_both_windows_are_tracked_independently(): void
    {
        Notification::fake();

        $appointment24h = $this->appointmentAt(now()->addHours(24));
        $appointment1h = $this->appointmentAt(now()->addHour());

        $this->artisan('appointments:send-reminders');

        Notification::assertSentTimes(AppointmentReminder::class, 2);
        $this->assertNotNull($appointment24h->fresh()->reminder_24h_sent_at);
        $this->assertNull($appointment24h->fresh()->reminder_1h_sent_at);
        $this->assertNotNull($appointment1h->fresh()->reminder_1h_sent_at);
        $this->assertNull($appointment1h->fresh()->reminder_24h_sent_at);
    }
}
