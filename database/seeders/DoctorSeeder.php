<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Curated from public/assets/: real clinician photos (white coats/stethoscopes), hand-picked
        // to exclude masked faces, casual outfits, or visible foreign clinic branding.
        $portraits = [
            'women' => [
                'female doc (2).jpg',
                'female doc (3).jpg',
                'female doc (4).jpg',
                'female doc (5).jpg',
                'female doc (6).jpg',
                'female doc (7).jpg',
            ],
            'men' => [
                'male doc.jpg',
                'male doc (3).jpg',
                'male doc (4).jpg',
                'male doc (6).jpg',
                'male doc (7).jpg',
            ],
        ];

        $roster = [
            ['name' => 'Dr. Anusha Perera', 'gender' => 'women', 'specialization' => 'Psychiatrist', 'years' => 12, 'fee' => 4500, 'mode' => 'both', 'rating' => 4.9, 'bio' => 'Consultant psychiatrist focused on mood disorders, anxiety and medication management.'],
            ['name' => 'Dr. S. Rajaratnam', 'gender' => 'men', 'specialization' => 'Clinical Psychologist', 'years' => 9, 'fee' => 3800, 'mode' => 'in_person', 'rating' => 5.0, 'bio' => 'Clinical psychologist specialising in trauma recovery and PTSD.'],
            ['name' => 'Ms. Dilani Fernando', 'gender' => 'women', 'specialization' => 'Counsellor', 'years' => 7, 'fee' => 3200, 'mode' => 'online', 'rating' => 4.8, 'bio' => 'Counsellor working with children and teenagers on anxiety and school stress.'],
            ['name' => 'Dr. Nuwan Bandara', 'gender' => 'men', 'specialization' => 'Psychiatrist', 'years' => 14, 'fee' => 4000, 'mode' => 'both', 'rating' => 4.7, 'bio' => 'Consultant psychiatrist with a special interest in addiction medicine.'],
            ['name' => 'Ms. Hasini Jayawardena', 'gender' => 'women', 'specialization' => 'Counsellor', 'years' => 6, 'fee' => 3500, 'mode' => 'both', 'rating' => 4.6, 'bio' => 'Counsellor supporting couples and individuals through relationship challenges.'],
            ['name' => 'Dr. Mahesh Kulasooriya', 'gender' => 'men', 'specialization' => 'Clinical Psychologist', 'years' => 8, 'fee' => 3600, 'mode' => 'in_person', 'rating' => 4.9, 'bio' => 'Clinical psychologist helping patients manage anxiety through CBT.'],
            ['name' => 'Dr. Chathurika Wickramasinghe', 'gender' => 'women', 'specialization' => 'Psychiatrist', 'years' => 11, 'fee' => 4200, 'mode' => 'both', 'rating' => 4.8, 'bio' => 'Consultant psychiatrist with a focus on postpartum mental health.'],
            ['name' => 'Mr. Ruwan Gunasekara', 'gender' => 'men', 'specialization' => 'Counsellor', 'years' => 5, 'fee' => 3000, 'mode' => 'online', 'rating' => 4.5, 'bio' => 'Counsellor specialising in workplace stress and burnout.'],
            ['name' => 'Dr. Priyanka Jayasuriya', 'gender' => 'women', 'specialization' => 'Clinical Psychologist', 'years' => 10, 'fee' => 3900, 'mode' => 'both', 'rating' => 4.7, 'bio' => 'Clinical psychologist working with adults on depression and anxiety.'],
            ['name' => 'Dr. Kavindu Rathnayake', 'gender' => 'men', 'specialization' => 'Psychiatrist', 'years' => 15, 'fee' => 4800, 'mode' => 'in_person', 'rating' => 4.9, 'bio' => 'Senior consultant psychiatrist with two decades in public and private practice.'],
            ['name' => 'Ms. Nimasha Wijesinghe', 'gender' => 'women', 'specialization' => 'Counsellor', 'years' => 4, 'fee' => 2800, 'mode' => 'online', 'rating' => 4.4, 'bio' => 'Counsellor supporting university students with stress and adjustment.'],
            ['name' => 'Dr. Thilina Abeysekera', 'gender' => 'men', 'specialization' => 'Clinical Psychologist', 'years' => 9, 'fee' => 3700, 'mode' => 'both', 'rating' => 4.6, 'bio' => 'Clinical psychologist specialising in OCD and anxiety disorders.'],
            ['name' => 'Dr. Vinod Selvarajah', 'gender' => 'men', 'specialization' => 'Psychiatrist', 'years' => 13, 'fee' => 4300, 'mode' => 'both', 'rating' => 4.8, 'bio' => 'Consultant psychiatrist with experience in geriatric mental health.'],
            ['name' => 'Ms. Ishara Karunaratne', 'gender' => 'women', 'specialization' => 'Counsellor', 'years' => 6, 'fee' => 3100, 'mode' => 'in_person', 'rating' => 4.5, 'bio' => 'Counsellor working with families on communication and conflict resolution.'],
            ['name' => 'Dr. Ashan Dissanayake', 'gender' => 'men', 'specialization' => 'Clinical Psychologist', 'years' => 8, 'fee' => 3600, 'mode' => 'online', 'rating' => 4.7, 'bio' => 'Clinical psychologist focused on sleep disorders and stress management.'],
        ];

        $password = Hash::make('password');
        $approver = Admin::first();
        $genderCounters = ['women' => 0, 'men' => 0];

        foreach ($roster as $entry) {
            $slug = Str::slug($entry['name']);
            $email = "{$slug}@psycare.test";
            $photoPath = "doctor-avatars/{$slug}.jpg";

            if (! Storage::disk('public')->exists($photoPath)) {
                $gender = $entry['gender'];
                $pool = $portraits[$gender];
                $sourceFile = $pool[$genderCounters[$gender] % count($pool)];
                $genderCounters[$gender]++;

                $sourcePath = public_path("assets/{$sourceFile}");

                if (File::exists($sourcePath)) {
                    Storage::disk('public')->put($photoPath, $this->resizedAvatar($sourcePath));
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

    /**
     * Downscale and centre-crop a source photo to a square JPEG suitable for an avatar,
     * so seeded doctors don't ship multi-megabyte, full-resolution images to every page.
     */
    private function resizedAvatar(string $sourcePath, int $size = 500): string
    {
        $source = imagecreatefromjpeg($sourcePath);
        $width = imagesx($source);
        $height = imagesy($source);
        $cropSize = min($width, $height);

        $destination = imagecreatetruecolor($size, $size);
        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            (int) (($width - $cropSize) / 2),
            (int) (($height - $cropSize) / 3),
            $size,
            $size,
            $cropSize,
            $cropSize,
        );

        ob_start();
        imagejpeg($destination, null, 85);
        $contents = ob_get_clean();

        imagedestroy($source);
        imagedestroy($destination);

        return $contents;
    }
}
