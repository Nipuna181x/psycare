<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roster = [
            [
                'clinic' => ['name' => 'Serene Mind Clinic', 'email' => 'contact@serenemind.test', 'phone' => '0112440001', 'address' => 'Colombo 07, Colombo'],
                'doctor' => [
                    'name' => 'Dr. Anusha Perera',
                    'specialization' => 'Psychiatry',
                    'avatar' => 'doc-1.jpg',
                    'years_experience' => 12,
                    'consultation_fee' => 4500,
                    'consultation_mode' => 'both',
                    'rating' => 4.9,
                    'bio' => 'Consultant psychiatrist focused on mood disorders, anxiety and medication management, with over a decade of hospital and private practice experience.',
                ],
            ],
            [
                'clinic' => ['name' => 'Northern Wellbeing Centre', 'email' => 'contact@northernwellbeing.test', 'phone' => '0212220002', 'address' => 'Jaffna Town, Jaffna'],
                'doctor' => [
                    'name' => 'Dr. S. Rajaratnam',
                    'specialization' => 'Trauma',
                    'avatar' => 'doc-2.jpg',
                    'years_experience' => 9,
                    'consultation_fee' => 3800,
                    'consultation_mode' => 'in_person',
                    'rating' => 5.0,
                    'bio' => 'Clinical psychologist specialising in trauma recovery and PTSD, using evidence-based talk therapy tailored to each patient.',
                ],
            ],
            [
                'clinic' => ['name' => 'Lagoon Counselling Rooms', 'email' => 'contact@lagooncounselling.test', 'phone' => '0312230003', 'address' => 'Negombo Beach Road, Negombo'],
                'doctor' => [
                    'name' => 'Ms. Dilani Fernando',
                    'specialization' => 'Child & teen',
                    'avatar' => 'doc-3.jpg',
                    'years_experience' => 7,
                    'consultation_fee' => 3200,
                    'consultation_mode' => 'online',
                    'rating' => 4.8,
                    'bio' => 'Counselling psychologist working with children and teenagers on anxiety, school stress and family transitions.',
                ],
            ],
            [
                'clinic' => ['name' => 'Hill Country Medical Institute', 'email' => 'contact@hillcountrymedical.test', 'phone' => '0812220004', 'address' => 'Kandy City Centre, Kandy'],
                'doctor' => [
                    'name' => 'Dr. Nuwan Bandara',
                    'specialization' => 'Psychiatry',
                    'avatar' => 'doc-4.jpg',
                    'years_experience' => 14,
                    'consultation_fee' => 4000,
                    'consultation_mode' => 'both',
                    'rating' => 4.7,
                    'bio' => 'Consultant psychiatrist with a special interest in addiction medicine and dual-diagnosis care.',
                ],
            ],
            [
                'clinic' => ['name' => 'Southern Care Collective', 'email' => 'contact@southerncare.test', 'phone' => '0912220005', 'address' => 'Galle Fort, Galle'],
                'doctor' => [
                    'name' => 'Ms. Hasini Jayawardena',
                    'specialization' => 'Counselling',
                    'avatar' => 'doc-5.jpg',
                    'years_experience' => 6,
                    'consultation_fee' => 3500,
                    'consultation_mode' => 'both',
                    'rating' => 4.6,
                    'bio' => 'Counselling psychologist supporting couples and individuals through relationship and communication challenges.',
                ],
            ],
            [
                'clinic' => ['name' => 'Eastern Mind Practice', 'email' => 'contact@easternmind.test', 'phone' => '0652220006', 'address' => 'Batticaloa Town, Batticaloa'],
                'doctor' => [
                    'name' => 'Dr. Mahesh Kulasooriya',
                    'specialization' => 'Counselling',
                    'avatar' => 'doc-6.jpg',
                    'years_experience' => 8,
                    'consultation_fee' => 3600,
                    'consultation_mode' => 'in_person',
                    'rating' => 4.9,
                    'bio' => 'Clinical psychologist helping patients manage anxiety and stress through cognitive behavioural therapy.',
                ],
            ],
        ];

        $password = Hash::make('password');

        foreach ($roster as $entry) {
            $medicalCenter = MedicalCenter::firstOrCreate(
                ['email' => $entry['clinic']['email']],
                [
                    'name' => $entry['clinic']['name'],
                    'phone' => $entry['clinic']['phone'],
                    'address' => $entry['clinic']['address'],
                    'registration_number' => 'REG-'.Str::upper(Str::random(6)),
                    'password' => $password,
                    'status' => 'approved',
                ]
            );

            Doctor::firstOrCreate(
                ['username' => Str::slug($entry['doctor']['name'])],
                [
                    'medical_center_id' => $medicalCenter->id,
                    'name' => $entry['doctor']['name'],
                    'email' => Str::slug($entry['doctor']['name']).'@psycare.test',
                    'password' => $password,
                    'specialization' => $entry['doctor']['specialization'],
                    'avatar' => $entry['doctor']['avatar'],
                    'years_experience' => $entry['doctor']['years_experience'],
                    'consultation_fee' => $entry['doctor']['consultation_fee'],
                    'consultation_mode' => $entry['doctor']['consultation_mode'],
                    'rating' => $entry['doctor']['rating'],
                    'bio' => $entry['doctor']['bio'],
                    'phone' => $entry['clinic']['phone'],
                    'status' => 'active',
                ]
            );
        }
    }
}
