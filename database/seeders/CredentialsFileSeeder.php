<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CredentialsFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Writes CREDENTIALS.md at the project root, listing every seeded login.
     * All seeded accounts share the password `password`.
     */
    public function run(): void
    {
        $lines = [
            '# PsyCare Seeded Credentials',
            '',
            'All accounts below use the password `password` unless noted otherwise.',
            '',
            '## Super Admin',
        ];

        foreach (Admin::all() as $admin) {
            $lines[] = "- {$admin->name} — {$admin->email} / password";
        }

        $lines[] = '';
        $lines[] = '## Clinic Admins';
        foreach (MedicalCenter::all() as $clinic) {
            $lines[] = "- {$clinic->name} — {$clinic->email} / password";
        }

        $lines[] = '';
        $lines[] = '## Doctors';
        foreach (Doctor::with('activeAffiliations.clinic')->orderBy('name')->get() as $doctor) {
            $clinics = $doctor->activeAffiliations->pluck('clinic.name')->implode(', ') ?: 'No active affiliations';
            $lines[] = "- {$doctor->name} ({$doctor->specialization}) — {$doctor->email} / password — Clinics: {$clinics}";
        }

        $lines[] = '';
        $lines[] = '## Patients';
        foreach (User::orderBy('name')->get() as $patient) {
            $lines[] = "- {$patient->name} — {$patient->email} / password";
        }

        $lines[] = '';

        File::put(base_path('CREDENTIALS.md'), implode("\n", $lines));
    }
}
