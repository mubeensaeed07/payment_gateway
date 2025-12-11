<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'saeedmubeen20@gmail.com'],
            [
                'name' => 'Super Admin',
                'email' => 'saeedmubeen20@gmail.com',
                'role' => 'superadmin',
                'email_verified_at' => now(),
            ]
        );
    }
}

