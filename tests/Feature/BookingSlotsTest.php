<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\DoctorAvailabilitySlot;
use App\Models\DoctorClinicAffiliation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSlotsTest extends TestCase
{
    use RefreshDatabase;

    private function operatingHours(string $day, ?string $opens, ?string $closes, bool $closed = false): array
    {
        return collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])
            ->map(fn ($d) => [
                'day' => $d,
                'opens' => $d === $day ? $opens : '09:00',
                'closes' => $d === $day ? $closes : '17:00',
                'closed' => $d === $day ? $closed : false,
            ])
            ->all();
    }

    public function test_slots_default_to_nine_to_five_when_clinic_has_not_set_hours(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        DoctorClinicAffiliation::factory()->for($doctor)->create();

        $date = now()->addWeek()->next('Monday')->toDateString();

        $this->actingAs($patient)->get(route('booking.schedule', $doctor));
        $response = $this->actingAs($patient)->getJson(route('booking.slots', $doctor).'?date='.$date);

        $response->assertOk();
        $times = collect($response->json('slots'))->pluck('time');
        $this->assertTrue($times->contains('09:00'));
        $this->assertFalse($times->contains('17:00'));
        $this->assertFalse($times->contains('08:30'));
    }

    public function test_slots_are_bounded_by_clinic_operating_hours(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $day = now()->addWeek()->next('Wednesday');
        $affiliation->clinic->update(['operating_hours' => $this->operatingHours('Wednesday', '10:00', '14:00')]);

        $this->actingAs($patient)->get(route('booking.schedule', $doctor));
        $response = $this->actingAs($patient)->getJson(route('booking.slots', $doctor).'?date='.$day->toDateString());

        $response->assertOk();
        $times = collect($response->json('slots'))->pluck('time');
        $this->assertTrue($times->contains('10:00'));
        $this->assertTrue($times->contains('13:30'));
        $this->assertFalse($times->contains('14:00'));
        $this->assertFalse($times->contains('09:30'));
    }

    public function test_no_slots_returned_when_clinic_is_closed_that_day(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $day = now()->addWeek()->next('Sunday');
        $affiliation->clinic->update(['operating_hours' => $this->operatingHours('Sunday', null, null, true)]);

        $this->actingAs($patient)->get(route('booking.schedule', $doctor));
        $response = $this->actingAs($patient)->getJson(route('booking.slots', $doctor).'?date='.$day->toDateString());

        $response->assertOk();
        $this->assertSame([], $response->json('slots'));
    }

    public function test_published_availability_slots_cannot_override_a_closed_clinic_day(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $day = now()->addWeek()->next('Sunday');
        $affiliation->clinic->update(['operating_hours' => $this->operatingHours('Sunday', null, null, true)]);

        DoctorAvailabilitySlot::factory()->create([
            'doctor_id' => $doctor->id,
            'clinic_id' => $affiliation->clinic_id,
            'date' => $day->toDateString(),
            'start_time' => '11:00',
            'is_booked' => false,
        ]);

        $this->actingAs($patient)->get(route('booking.schedule', $doctor));
        $response = $this->actingAs($patient)->getJson(route('booking.slots', $doctor).'?date='.$day->toDateString());

        $response->assertOk();
        $this->assertSame([], $response->json('slots'));
    }

    public function test_updated_clinic_hours_immediately_replace_old_published_slot_times(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $day = now()->addWeek()->next('Wednesday');

        foreach (['09:00', '10:00', '15:00'] as $time) {
            DoctorAvailabilitySlot::factory()->create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $affiliation->clinic_id,
                'date' => $day->toDateString(),
                'start_time' => $time,
                'is_booked' => false,
            ]);
        }

        $this->actingAs($patient)->get(route('booking.schedule', $doctor));

        $this->actingAs($affiliation->clinic, 'medical_center')
            ->patch(route('medical-center.settings.hours.update'), [
                'hours' => $this->operatingHours('Wednesday', '10:00', '12:00'),
            ])
            ->assertSessionHasNoErrors();

        $response = $this->actingAs($patient)->getJson(route('booking.slots', $doctor).'?date='.$day->toDateString());

        $response->assertOk();
        $times = collect($response->json('slots'))->pluck('time');
        $this->assertSame(['10:00', '10:30', '11:00', '11:30'], $times->all());
        $this->assertFalse($times->contains('09:00'));
        $this->assertFalse($times->contains('15:00'));
    }

    public function test_patient_cannot_submit_a_time_outside_current_clinic_hours(): void
    {
        $patient = User::factory()->create();
        $doctor = Doctor::factory()->create();
        $affiliation = DoctorClinicAffiliation::factory()->for($doctor)->create();
        $day = now()->addWeek()->next('Wednesday');
        $affiliation->clinic->update(['operating_hours' => $this->operatingHours('Wednesday', '10:00', '12:00')]);

        $this->actingAs($patient)->get(route('booking.schedule', $doctor));

        $this->actingAs($patient)
            ->post(route('booking.schedule', $doctor), [
                'appointment_date' => $day->toDateString(),
                'appointment_time' => '09:00',
            ])
            ->assertSessionHasErrors('appointment_time');
    }
}
