<?php

namespace Database\Seeders;

use App\Models\DoctorAvailabilitySlot;
use App\Models\DoctorClinicAffiliation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AvailabilitySlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Generates 30-minute slots, 9AM-5PM, for the next 2 weeks for every
     * active doctor+clinic pairing, with roughly 20% marked as already booked.
     */
    public function run(): void
    {
        $affiliations = DoctorClinicAffiliation::where('status', 'active')->get();

        foreach ($affiliations as $affiliation) {
            for ($day = 0; $day < 14; $day++) {
                $date = now()->addDays($day)->toDateString();
                $cursor = Carbon::parse($date)->setTime(9, 0);
                $end = Carbon::parse($date)->setTime(17, 0);

                while ($cursor->lt($end)) {
                    DoctorAvailabilitySlot::firstOrCreate(
                        [
                            'doctor_id' => $affiliation->doctor_id,
                            'clinic_id' => $affiliation->clinic_id,
                            'date' => $date,
                            'start_time' => $cursor->format('H:i:s'),
                        ],
                        [
                            'end_time' => $cursor->copy()->addMinutes(30)->format('H:i:s'),
                            'is_booked' => fake()->numberBetween(1, 100) <= 20,
                        ]
                    );

                    $cursor->addMinutes(30);
                }
            }
        }
    }
}
