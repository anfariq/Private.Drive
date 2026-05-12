<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Andi',
            'email' => 'andi@drive.Super',
            'password' => Hash::make('Regina123'), // Ganti dengan password yang kamu inginkan
            'role' => 'admin',
            'is_verified' => true, // Di Postgres gunakan boolean true
            'storage_limit' => 200, // Sesuai gambar: 200 GB
            'email_verified_at' => '2026-04-08 13:34:18',
        ]);
    }
}
