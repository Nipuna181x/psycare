<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'admin@psycare.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
