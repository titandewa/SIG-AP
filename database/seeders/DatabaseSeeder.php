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
        // 1. Akun Aspri (Tetap)
        User::create([
            'name' => 'Administrator',
            'email' => 'administrator@sigap.com',
            'phone' => '081234567890',
            'role' => 'aspri',
            'password' => Hash::make('password'),
        ]);

        // Daftar Petinggi lengkap dengan nomor HP dari data Anda
        $petinggiList = [
            ['name' => 'Evine Fitriani Putri Ayu Kusuma Dewi', 'email' => 'evine@sigap.com', 'phone' => '6285536952566'],
            ['name' => 'Son Harianto', 'email' => 'son@sigap.com', 'phone' => '6281246664367'],
            ['name' => 'Moh. Abdul Gafur', 'email' => 'gafur@sigap.com', 'phone' => '6282330502662'],
            ['name' => 'Moh Adi Hariyanto', 'email' => 'adi@sigap.com', 'phone' => '6281217368771'],
            ['name' => 'Ahmad Dendy Relly M', 'email' => 'dendy@sigap.com', 'phone' => '6281515117001'],
            ['name' => 'Jaya Radiman', 'email' => 'jaya@sigap.com', 'phone' => '6285876081407'],
            ['name' => 'Nur Hidayah', 'email' => 'nur.h@sigap.com', 'phone' => '6285607822692'],
            ['name' => 'Asmarawan', 'email' => 'asmarawan@sigap.com', 'phone' => '6285704662851'],
            ['name' => 'Habibullah', 'email' => 'habib@sigap.com', 'phone' => '6283114272133'],
            ['name' => 'Mohammad Ivan Renaldi', 'email' => 'ivan@sigap.com', 'phone' => '6285393763403'],
            ['name' => 'Mukhammad Ariansyah Wahyu Hidayah', 'email' => 'ariansyah@sigap.com', 'phone' => '6289650466791'],
            ['name' => 'Rachmad Hinjaya', 'email' => 'rachmad@sigap.com', 'phone' => '6282181083867'],
            ['name' => 'Yahdillah Al Muhtadi', 'email' => 'yahdillah@sigap.com', 'phone' => '6285782630171'],
            ['name' => 'Rio Mardi Aji Subekti', 'email' => 'rio@sigap.com', 'phone' => '6287795452982'],
            ['name' => 'Khomarudin', 'email' => 'khomarudin@sigap.com', 'phone' => '6289541334602'],
            ['name' => 'Nur Fajrina Rizqiyani', 'email' => 'fajrina@sigap.com', 'phone' => '6285729940797'],
            ['name' => 'Alham Amirudin', 'email' => 'alham@sigap.com', 'phone' => null],
            ['name' => 'Gio Vena', 'email' => 'giovena@sigap.com', 'phone' => '6282244465931'],
            ['name' => 'Ahmad Rizaldi Fahreza', 'email' => 'rizaldi@sigap.com', 'phone' => '6282140966863'],
            ['name' => 'Makhrifatul Murdhita', 'email' => 'makhrifatul@sigap.com', 'phone' => '62881036760497'],
            ['name' => 'Rizky Trisanti', 'email' => 'rizky@sigap.com', 'phone' => '6285604102170'],
            ['name' => 'Halimah Madinatul Islam', 'email' => 'halimah@sigap.com', 'phone' => '6285880355346'],
            ['name' => 'Rismalinda Febiarini', 'email' => 'rismalinda@sigap.com', 'phone' => '6283851350877'],
            ['name' => 'Prasetya Eka Prabowo', 'email' => 'prasetya@sigap.com', 'phone' => '628999998060'],
            ['name' => 'Betty Khoirun Nisa', 'email' => 'betty@sigap.com', 'phone' => '628977222037'],
            ['name' => 'Mahendra Kumala', 'email' => 'mahendra@sigap.com', 'phone' => '628113332434'],
            ['name' => 'Afanin Nur Raudhah', 'email' => 'afanin@sigap.com', 'phone' => '6285190445453'],
            ['name' => 'Yeni isnawati', 'email' => 'yeni@sigap.com', 'phone' => '6281331009455'],
        ];

        foreach ($petinggiList as $petinggi) {
            User::create([
                'name'     => $petinggi['name'],
                'email'    => $petinggi['email'],
                'phone'    => $petinggi['phone'],
                'role'     => 'petinggi',
                'password' => Hash::make('password'),
            ]);
        }

        // 4. Akun Direksi
        User::create([
            'name' => 'Direksi',
            'email' => 'direksi@sigap.com',
            'phone' => '081234567893',
            'role' => 'dirut',
            'password' => Hash::make('password'),
        ]);
    }
}