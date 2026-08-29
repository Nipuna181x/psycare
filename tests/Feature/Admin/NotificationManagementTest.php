<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Doctor;
use App\Notifications\AdminApprovalRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_center_registration_notifies_all_admins(): void
    {
        $admins = Admin::factory()->count(2)->create();
        Notification::fake();

        $this->post(route('medical-center.register'), [
            'name' => 'Hope Clinic',
            'email' => 'hope@example.com',
            'phone' => '0711234567',
            'address' => '123 Main Street',
            'registration_number' => 'REG-9001',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('medical-center.login'));

        Notification::assertSentTo($admins, AdminApprovalRequested::class, function (AdminApprovalRequested $notification): bool {
            return $notification->type === 'medical_center_application'
                && str_contains($notification->message, 'Hope Clinic')
                && str_starts_with($notification->link, '/admin/medical-centers/');
        });
    }

    public function test_completed_doctor_application_notifies_admin_once(): void
    {
        $admin = Admin::factory()->create();
        $doctor = Doctor::factory()->pendingApproval()->create();
        Notification::fake();

        $payload = [
            'specialization' => 'Clinical Psychology',
            'bio' => 'Experienced clinician.',
            'years_of_experience' => 8,
        ];

        $this->actingAs($doctor, 'doctor')->patch(route('doctor.onboarding.update'), $payload)->assertRedirect();
        $this->actingAs($doctor, 'doctor')->patch(route('doctor.onboarding.update'), $payload)->assertRedirect();

        Notification::assertSentToTimes($admin, AdminApprovalRequested::class, 1);
    }

    public function test_admin_can_view_and_open_an_unread_notification(): void
    {
        $admin = Admin::factory()->create();
        $doctor = Doctor::factory()->create();
        $admin->notifyNow(new AdminApprovalRequested(
            type: 'doctor_application',
            message: "Dr. {$doctor->name} is ready for approval.",
            link: route('admin.doctors.show', $doctor, absolute: false),
            subjectId: $doctor->id,
        ));

        $notification = $admin->notifications()->firstOrFail();

        $this->actingAs($admin, 'admin')->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee($doctor->name)
            ->assertSee('1 unread', false);

        $this->actingAs($admin, 'admin')->post(route('admin.notifications.read', $notification->id))
            ->assertRedirect(route('admin.doctors.show', $doctor, absolute: false));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $admin = Admin::factory()->create();
        $admin->notifyNow(new AdminApprovalRequested(
            type: 'medical_center_application',
            message: 'A new medical center application is ready.',
            link: route('admin.medical-centers.index', absolute: false),
            subjectId: 1,
        ));

        $this->actingAs($admin, 'admin')->post(route('admin.notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $admin->unreadNotifications()->count());
    }
}
