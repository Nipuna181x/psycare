<?php

namespace Tests\Feature\MedicalCenter;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_zero_appointments(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk();
    }

    public function test_appointments_this_month_count_is_correct(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'appointment_date' => now()->format('Y-m-d')]);
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'appointment_date' => now()->format('Y-m-d')]);
        Appointment::factory()->for($doctor)->create(['medical_center_id' => $clinic->id, 'appointment_date' => now()->subMonths(2)->format('Y-m-d')]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk()->assertSeeText('2');
    }

    public function test_revenue_sums_only_completed_appointments(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->completed()->create([
            'medical_center_id' => $clinic->id,
            'appointment_date' => now()->format('Y-m-d'),
            'clinic_fee_charged' => 1000,
        ]);
        Appointment::factory()->for($doctor)->create([
            'medical_center_id' => $clinic->id,
            'status' => 'confirmed',
            'appointment_date' => now()->format('Y-m-d'),
            'clinic_fee_charged' => 5000,
        ]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk()->assertSee('LKR 1,000.00')->assertDontSee('LKR 6,000.00');
    }

    public function test_busiest_doctors_ranked_correctly(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $busyDoctor = Doctor::factory()->create(['name' => 'Dr. Busy']);
        $quietDoctor = Doctor::factory()->create(['name' => 'Dr. Quiet']);
        Appointment::factory()->for($busyDoctor)->count(3)->create(['medical_center_id' => $clinic->id]);
        Appointment::factory()->for($quietDoctor)->count(1)->create(['medical_center_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk()->assertSeeInOrder(['Dr. Busy', 'Dr. Quiet']);
    }

    public function test_cancellation_rate_computed_correctly(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->cancelled()->count(2)->create(['medical_center_id' => $clinic->id]);
        Appointment::factory()->for($doctor)->completed()->count(2)->create(['medical_center_id' => $clinic->id]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk()->assertSee('50%');
    }

    public function test_volume_trend_has_six_months(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk();
        $expectedLabels = collect(range(5, 0))->map(fn ($m) => now()->subMonths($m)->format('M'))->all();
        foreach ($expectedLabels as $label) {
            $response->assertSee($label);
        }
    }

    public function test_another_clinics_appointments_never_influence_numbers(): void
    {
        $clinic = MedicalCenter::factory()->approved()->create();
        $otherClinic = MedicalCenter::factory()->approved()->create();
        $doctor = Doctor::factory()->create();
        Appointment::factory()->for($doctor)->count(5)->create([
            'medical_center_id' => $otherClinic->id,
            'appointment_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($clinic, 'medical_center')->get(route('medical-center.analytics.index'));

        $response->assertOk()->assertSeeText('0');
    }
}
