<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roster = [
            ['name' => 'Dr. Anusha Perera', 'specialization' => 'Psychiatrist', 'years' => 12, 'fee' => 4500, 'mode' => 'both', 'rating' => 4.9, 'bio' => 'Consultant psychiatrist focused on mood disorders, anxiety and medication management.'],
            ['name' => 'Dr. S. Rajaratnam', 'specialization' => 'Clinical Psychologist', 'years' => 9, 'fee' => 3800, 'mode' => 'in_person', 'rating' => 5.0, 'bio' => 'Clinical psychologist specialising in trauma recovery and PTSD.'],
            ['name' => 'Ms. Dilani Fernando', 'specialization' => 'Counsellor', 'years' => 7, 'fee' => 3200, 'mode' => 'online', 'rating' => 4.8, 'bio' => 'Counsellor working with children and teenagers on anxiety and school stress.'],
            ['name' => 'Dr. Nuwan Bandara', 'specialization' => 'Psychiatrist', 'years' => 14, 'fee' => 4000, 'mode' => 'both', 'rating' => 4.7, 'bio' => 'Consultant psychiatrist with a special interest in addiction medicine.'],
            ['name' => 'Ms. Hasini Jayawardena', 'specialization' => 'Counsellor', 'years' => 6, 'fee' => 3500, 'mode' => 'both', 'rating' => 4.6, 'bio' => 'Counsellor supporting couples and individuals through relationship challenges.'],
            ['name' => 'Dr. Mahesh Kulasooriya', 'specialization' => 'Clinical Psychologist', 'years' => 8, 'fee' => 3600, 'mode' => 'in_person', 'rating' => 4.9, 'bio' => 'Clinical psychologist helping patients manage anxiety through CBT.'],
            ['name' => 'Dr. Chathurika Wickramasinghe', 'specialization' => 'Psychiatrist', 'years' => 11, 'fee' => 4200, 'mode' => 'both', 'rating' => 4.8, 'bio' => 'Consultant psychiatrist with a focus on postpartum mental health.'],
            ['name' => 'Mr. Ruwan Gunasekara', 'specialization' => 'Counsellor', 'years' => 5, 'fee' => 3000, 'mode' => 'online', 'rating' => 4.5, 'bio' => 'Counsellor specialising in workplace stress and burnout.'],
            ['name' => 'Dr. Priyanka Jayasuriya', 'specialization' => 'Clinical Psychologist', 'years' => 10, 'fee' => 3900, 'mode' => 'both', 'rating' => 4.7, 'bio' => 'Clinical psychologist working with adults on depression and anxiety.'],
            ['name' => 'Dr. Kavindu Rathnayake', 'specialization' => 'Psychiatrist', 'years' => 15, 'fee' => 4800, 'mode' => 'in_person', 'rating' => 4.9, 'bio' => 'Senior consultant psychiatrist with two decades in public and private practice.'],
            ['name' => 'Ms. Nimasha Wijesinghe', 'specialization' => 'Counsellor', 'years' => 4, 'fee' => 2800, 'mode' => 'online', 'rating' => 4.4, 'bio' => 'Counsellor supporting university students with stress and adjustment.'],
            ['name' => 'Dr. Thilina Abeysekera', 'specialization' => 'Clinical Psychologist', 'years' => 9, 'fee' => 3700, 'mode' => 'both', 'rating' => 4.6, 'bio' => 'Clinical psychologist specialising in OCD and anxiety disorders.'],
            ['name' => 'Dr. Vinod Selvarajah', 'specialization' => 'Psychiatrist', 'years' => 13, 'fee' => 4300, 'mode' => 'both', 'rating' => 4.8, 'bio' => 'Consultant psychiatrist with experience in geriatric mental health.'],
            ['name' => 'Ms. Ishara Karunaratne', 'specialization' => 'Counsellor', 'years' => 6, 'fee' => 3100, 'mode' => 'in_person', 'rating' => 4.5, 'bio' => 'Counsellor working with families on communication and conflict resolution.'],
            ['name' => 'Dr. Ashan Dissanayake', 'specialization' => 'Clinical Psychologist', 'years' => 8, 'fee' => 3600, 'mode' => 'online', 'rating' => 4.7, 'bio' => 'Clinical psychologist focused on sleep disorders and stress management.'],
        ];

        $password = Hash::make('password');
        $approver = Admin::first();

        foreach ($roster as $entry) {
            $slug = Str::slug($entry['name']);
            $email = "{$slug}@psycare.test";
            $photoPath = "doctor-avatars/{$slug}.jpg";

            if (! Storage::disk('public')->exists($photoPath)) {
                try {
                    $response = Http::timeout(10)->get("https://i.pravatar.cc/300?u={$email}");

                    if ($response->successful()) {
                        Storage::disk('public')->put($photoPath, $response->body());
                    }
                } catch (\Throwable) {
                    // Network unavailable during seeding — profile_photo stays null and avatarUrl() falls back to initials.
                }
            }

            Doctor::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $entry['name'],
                    'password' => $password,
                    'license_number' => 'SLMC-'.fake()->unique()->numerify('####'),
                    'phone' => '07'.fake()->numerify('########'),
                    'specialization' => $entry['specialization'],
                    'bio' => $entry['bio'],
                    'profile_photo' => Storage::disk('public')->exists($photoPath) ? $photoPath : null,
                    'years_of_experience' => $entry['years'],
                    'consultation_fee' => $entry['fee'],
                    'consultation_mode' => $entry['mode'],
                    'rating' => $entry['rating'],
                    'status' => 'approved',
                    'onboarding_step' => 'profile_complete',
                    'approved_at' => now(),
                    'approved_by' => $approver?->id,
                ]
            );
        }
    }
}
