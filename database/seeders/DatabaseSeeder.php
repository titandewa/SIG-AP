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
<<<<<<< Updated upstream
            'email' => 'administrator@sigap.com',
=======
            'email' => 'admin@sigap.com',
>>>>>>> Stashed changes
            'phone' => '081234567890',
            'role' => 'aspri',
            'password' => Hash::make('password'),
        ]);

<<<<<<< Updated upstream
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
=======
        // 2. Akun Petinggi 1 (Legal/Hukum)
        User::create([
            'name' => 'Evine Fitriani Putri Ayu Kusuma Dewi(Corporate Relation Officer)',
            'email' => 'fitriani@sigap.com',
            'phone' => '6285536952566',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Son Harianto(Densus)',
            'email' => 'sonharianto@sigap.com',
            'phone' => '6281246664367',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        // 3. Akun Petinggi 2 (Keuangan)
        User::create([
            'name' => 'Moh. Abdul Gafur(PJ Brand AGN & Becar)',
            'email' => 'abdul@sigap.com',
            'phone' => '6282330502662',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Moh Adi Hariyanto(PJ Brand APM Nungky)',
            'email' => 'adi@sigap.com',
            'phone' => '6281217368771',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Ahmad Dendy Relly M(PJ Brand Dadar Beredar & Lamak Rasa)',
            'email' => 'dendy@sigap.com',
            'phone' => '6281515117001',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Jaya Radiman(PJ Brand Taburai & Kejar Setoran)',
            'email' => 'jayaradiman@sigap.com',
            'phone' => '6285876081407',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Nur Hidayah(PJ Support Business)',
            'email' => 'nurhidayah@sigap.com',
            'phone' => '6285607822692',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Asmarawan(Regional Manager)',
            'email' => 'asmarawan@sigap.com',
            'phone' => '6285704662851',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Habibullah(Regional Manager)',
            'email' => 'habibullah@sigap.com',
            'phone' => '6283114272133',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Mohammad Ivan Renaldi(Regional Manager)',
            'email' => 'ivanrenaldi@sigap.com',
            'phone' => '6285393763403',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

         User::create([
            'name' => 'Mukhammad Ariansyah Wahyu Hidayah(Regional Manager)',
            'email' => 'ariansyah@sigap.com',
            'phone' => '6289650466791',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Rachmad Hinjaya(Regional Manager)',
            'email' => 'rachmadhin@sigap.com',
            'phone' => '6282181083867',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Yahdillah Al Muhtadi(Regional Manager)',
            'email' => 'almuhtadi@sigap.com',
            'phone' => '6285782630171',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Rio Mardi Aji Subekti(Regional Manager)',
            'email' => 'mardiaji@sigap.com',
            'phone' => '6287795452982',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Khomarudin(Regional Manager & PJ Brand GKR)',
            'email' => 'khomarudin@sigap.com',
            'phone' => '62895413346021',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

          User::create([
            'name' => 'Nur Fajrina Rizqiyani(SPV Support Bisnis )',
            'email' => 'fajrina@sigap.com',
            'phone' => '6285729940797',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

          User::create([
            'name' => 'Makhrifatul Murdhita(Manager IT)',
            'email' => 'dhita@sigap.com',
            'phone' => '0881036760497',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Afanin Nur Raudhah(Vice President)',
            'email' => 'nurraudhah@sigap.com',
            'phone' => '6285190445453',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Mahendra Kumala(General Manager )',
            'email' => 'mahendrakumala@sigap.com',
            'phone' => '628113332434',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Giovenna(Manager Keuangan)',
            'email' => 'giovenna@sigap.com',
            'phone' => '6282244465931',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Ahmad Rizaldi Fahreza(Manager Bisdev)',
            'email' => 'rizaldi@sigap.com',
            'phone' => '6282140966863',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Rizky Tristanti(Manager HRD)',
            'email' => 'rizkytristanti@sigap.com',
            'phone' => '6285604102170',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Betty Khoirun Nissa(Manager Produksi)',
            'email' => 'betty@sigap.com',
            'phone' => '628977222037',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Rismalinda Febri Arini(Manager Pelatihan)',
            'email' => 'rismalinda@sigap.com',
            'phone' => '6283851350877',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Prasetya Eka Prabowo(Manager Pelatihan) ',
            'email' => 'prasetya@sigap.com',
            'phone' => '628999998060',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Halimah Madinatul Islam(Manager MKT) ',
            'email' => 'halimah@sigap.com',
            'phone' => '6285880355346',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

          User::create([
            'name' => 'Alham Amirudin(Staf Legal)',
            'email' => 'amirudin@sigap.com',
            'phone' => '6281233583526',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Yeni isnawati(First President)',
            'email' => 'yeniisnawati@sigap.com',
            'phone' => '6281331009455',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);
>>>>>>> Stashed changes

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