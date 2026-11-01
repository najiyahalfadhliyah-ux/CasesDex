<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Arsip',
            'email' => 'admin@arsip.test',
            'username' => 'admin',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Petugas Satu',
            'email' => 'petugas@arsip.test',
            'username' => 'petugas1',
            'password' => 'password123',
            'role' => 'petugas',
            'is_active' => true,
        ]);
    }
}