<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports_with_empty_data(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->get(route('admin.reports.index'))->assertOk()->assertSee('Reports &amp; Analytics', false);
    }

    public function test_reports_show_populated_platform_metrics(): void
    {
        $admin = Admin::factory()->create();
        $appointment = Appointment::factory()->create(['status' => 'completed']);
        Payment::factory()->succeeded()->create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'clinic_id' => $appointment->medical_center_id,
            'patient_id' => $appointment->user_id,
            'amount' => 4500,
        ]);

        $this->actingAs($admin, 'admin')->get(route('admin.reports.index'))->assertOk()->assertSee('LKR 4,500');
    }
}
