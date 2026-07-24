<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Aspri (Pengaju SK)
        User::create([
            'name' => 'Aspri Utama',
            'email' => 'aspri@sigap.com',
            'phone' => '081234567890',
            'role' => 'aspri',
            'password' => Hash::make('password'),
        ]);

        // 2. Akun Petinggi 1 (Legal/Hukum)
        User::create([
            'name' => 'Pak Budi (Legal)',
            'email' => 'budi@sigap.com',
            'phone' => '081234567891',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        // 3. Akun Petinggi 2 (Keuangan)
        User::create([
            'name' => 'Pak Andi (Keuangan)',
            'email' => 'andi@sigap.com',
            'phone' => '081234567892',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        // 4. Akun Dirut (Final Approver)
        User::create([
            'name' => 'Direktur Utama',
            'email' => 'dirut@sigap.com',
            'phone' => '081234567893',
            'role' => 'dirut',
            'password' => Hash::make('password'),
        ]);
    }
}