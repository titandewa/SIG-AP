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
            'name' => 'Hasanah (ADMIN IT & Operasional)',
            'email' => 'hasanah@sigap.com',
            'phone' => '083834557054',
            'role' => 'aspri',
            'password' => Hash::make('hasanah'),
        ]);

        User::create([
            'name' => 'SAJNATYA MAGHFIRA (ADMIN INVENTARIS PRODUKSI)',
            'email' => 'sajnatya01@gmail.com',
            'phone' => '082257786667',
            'role' => 'aspri',
            'password' => Hash::make('pengajuanSPD'),
        ]);

         User::create([
            'name' => 'AZIZAH INDRIANNY PUTRI (PIC DENSUS TRAINING DAN INKUBASI)',
            'email' => 'opsiazizah@gmail.com',
            'phone' => '082335813978',
            'role' => 'aspri',
            'password' => Hash::make('Tni0205'),
        ]);

        User::create([
            'name' => 'AYU FADHILAH (PIC INVENTARIS & ASET PRODUKSI)',
            'email' => 'admproduksiholding@gmail.com',
            'phone' => '089669788635',
            'role' => 'aspri',
            'password' => Hash::make('301297'),
        ]);

        User::create([
            'name' => 'RIKA DWI CAHYA NUR USMAN (AUDIT INTERNAL KEUANGAN)',
            'email' => 'team.au00@gmail.com',
            'phone' => '081818855460',
            'role' => 'aspri',
            'password' => Hash::make('baik12345'),
        ]);

        User::create([
            'name' => 'ENGGAR RAHMADANI (PIC KEUANGAN)',
            'email' => 'keuangannelongso@gmail.com',
            'phone' => '085708171189',
            'role' => 'aspri',
            'password' => Hash::make('baik12345'),
        ]);

        User::create([
            'name' => 'SUCI AYU LEATRI (ADMIN HRD)',
            'email' => 'recruitmentpusat.abc18@gmail.com',
            'phone' => '081378085687',
            'role' => 'aspri',
            'password' => Hash::make('Recruitment1234'),
        ]);

        User::create([
            'name' => 'DHIAN CAYADEVA PRASETYANANDINI (ADMIN KEMITRAAN BISDEV)',
            'email' => 'kemitraanayamnelongso@gmail.com',
            'phone' => '087717390992',
            'role' => 'aspri',
            'password' => Hash::make('qwertyuiop'),
        ]);

         User::create([
            'name' => 'VELLY (ADMIN DATA ANALYST MARKETING)',
            'email' => 'vellytakurniawati@gmail.com',
            'phone' => '081224998181',
            'role' => 'aspri',
            'password' => Hash::make('marketingsigap1'),
        ]);

        User::create([
            'name' => 'NADA ANGGRAENI (PIC ADMIN OUTLET PRODUKSI)',
            'email' => 'nadaanggraeni19@gmail.com',
            'phone' => '082257091172',
            'role' => 'aspri',
            'password' => Hash::make('nadacantik1901'),
        ]);

        User::create([
            'name' => 'AVITA AINI NUR ILMI (ADMIN DATA ANALYST MARKETING)',
            'email' => 'avitaaini6@gmail.com',
            'phone' => '085732927785',
            'role' => 'aspri',
            'password' => Hash::make('marketingsigap2'),
        ]);

        User::create([
            'name' => 'DEVITA IVO SUKMA (ADMIN BUSINESS DEVELOPMENT)',
            'email' => 'admbisdevabc08@gmail.com',
            'phone' => '089699521762',
            'role' => 'aspri',
            'password' => Hash::make('adminbisdevvita'),
        ]);

        User::create([
            'name' => 'DWIKA RENANDYA TRIAS ANDRIAN (ADMIN BUSINESS DEVELOPMENT)',
            'email' => 'admbisdevabc08gmail.com',
            'phone' => '081235109780',
            'role' => 'aspri',
            'password' => Hash::make('adminbisdevrere'),
        ]);

        // 2. Akun Petinggi 1 (Legal/Hukum)
        User::create([
            'name' => 'Evine Fitriani Putri Ayu Kusuma Dewi (COORDINATOR HOLDING)',
            'email' => 'evinfitriani0@gmail.com',
            'phone' => '6285536952566',
            'role' => 'petinggi',
            'password' => Hash::make('MITRAABC123'),
        ]);

        User::create([
            'name' => 'Son Harianto (Densus)',
            'email' => 'sonharianto@sigap.com',
            'phone' => '6281246664367',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        // 3. Akun Petinggi 2 (Keuangan)
        User::create([
            'name' => 'Moh. Abdul Gafur (PJ Brand AGN & Becar HOLDING)',
            'email' => 'mohabdul.gafur11@gmail.com',
            'phone' => '6282330502662',
            'role' => 'petinggi',
            'password' => Hash::make('110394'),
        ]);

        User::create([
            'name' => 'Moh Adi Hariyanto (PJ Brand AP Mami Nungky)',
            'email' => 'mohadihariyanto@gmail.com',
            'phone' => '6281217368771',
            'role' => 'petinggi',
            'password' => Hash::make('Pojur1993'),
        ]);

        User::create([
            'name' => 'Ahmad Dendy Relly M (PJ Brand Dadar Beredar & Lamak Rasa HOLDING)',
            'email' => 'ahmaddendy07@gmail.com',
            'phone' => '6281515117001',
            'role' => 'petinggi',
            'password' => Hash::make('Sumberinginkidul11'),
        ]);

        User::create([
            'name' => 'Jaya Radiman (PJ Brand Taburai & Kejar Setoran)',
            'email' => 'jayaradiman@sigap.com',
            'phone' => '6285876081407',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Nur Hidayah (PJ Support Business)',
            'email' => 'nurhidayah@sigap.com',
            'phone' => '6285607822692',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

         User::create([
            'name' => 'Al luthfi dwi kurnia putra (COORDINATOR BISDEV)',
            'email' => 'alluthfi.bisdev@sigap.com',
            'phone' => '082335331994',
            'role' => 'petinggi',
            'password' => Hash::make('123456'),
        ]);

        User::create([
            'name' => 'Asmarawan (Regional Manager)',
            'email' => 'asmarakunkerja2510@gmail.com',
            'phone' => '6285704662851',
            'role' => 'petinggi',
            'password' => Hash::make('D4n9e12'),
        ]);

        User::create([
            'name' => 'Habibullah (REGIONAL MANAGER HOLDING)',
            'email' => 'habibullahalfarabi@gmail.com',
            'phone' => '6283114272133',
            'role' => 'petinggi',
            'password' => Hash::make('83151'),
        ]);

        User::create([
            'name' => 'Mohammad Ivan Renaldi (REGIONAL MANAGER HOLDING)',
            'email' => 'ivanyesinta@gmail.com',
            'phone' => '6285393763403',
            'role' => 'petinggi',
            'password' => Hash::make('Tobel250896'),
        ]);

         User::create([
            'name' => 'Mukhammad Ariansyah Wahyu Hidayah (REGIONAL MANAGER HOLDING)',
            'email' => 'erikcdewa41@gmail.com',
            'phone' => '6289650466791',
            'role' => 'petinggi',
            'password' => Hash::make('baik4141'),
        ]);

        User::create([
            'name' => 'Rachmad Hinjaya (REGIONAL MANAGER HOLDING)',
            'email' => 'rachmadhinjaya78@gmail.com',
            'phone' => '6282181083867',
            'role' => 'petinggi',
            'password' => Hash::make('181920'),
        ]);

        User::create([
            'name' => 'Yahdillah Al Muhtadi (REGIONAL MANAGER OPERASIONAL)',
            'email' => 'yahdillahalmuhtadi@gmail.com',
            'phone' => '6285782630171',
            'role' => 'petinggi',
            'password' => Hash::make('JawaTengah01'),
        ]);

        User::create([
            'name' => 'Rio Mardi Aji Subekti (Regional Manager)',
            'email' => 'riosubekti.kerja@gmail.com',
            'phone' => '6287795452982',
            'role' => 'petinggi',
            'password' => Hash::make('115599'),
        ]);

        User::create([
            'name' => 'Khomarudin (Regional Manager & PJ Brand GKR)',
            'email' => 'khomarudin@sigap.com',
            'phone' => '62895413346021',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'ANNISA FIRDAUS (STAF KEUANGAN)',
            'email' => 'annisa.fdrs@gmail.com',
            'phone' => '089506942534',
            'role' => 'petinggi',
            'password' => Hash::make('annisa16*'),
        ]);

          User::create([
            'name' => 'Nur Fajrina Rizqiyani (SPV Support Bisnis )',
            'email' => 'fajrina@sigap.com',
            'phone' => '6285729940797',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

          User::create([
            'name' => 'Makhrifatul Murdhita (Manager IT)',
            'email' => 'dhita@sigap.com',
            'phone' => '0881036760497',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Afanin Nur Raudhah (Vice President)',
            'email' => 'nurraudhah@sigap.com',
            'phone' => '6285190445453',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Mahendra Kumala (General Manager)',
            'email' => 'mahendrakumala@sigap.com',
            'phone' => '628113332434',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'DEWI KUMALASARI (STAF KEUANGAN)',
            'email' => 'dadaruntuksemua@gmail.com',
            'phone' => '082245558483',
            'role' => 'petinggi',
            'password' => Hash::make('@Arshaameera123'),
        ]);

        User::create([
            'name' => 'Giovenna (Coordinator Keuangan)',
            'email' => 'haegyo1516@gmail.com',
            'phone' => '6282244465931',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Ahmad Rizaldi Fahreza (Manager Bisdev)',
            'email' => 'rizaldi@sigap.com',
            'phone' => '6282140966863',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Rizky Trisanti (Coordinator HRD)',
            'email' => 'trisantirizky@gmail.com',
            'phone' => '6285604102170',
            'role' => 'petinggi',
            'password' => Hash::make('Malang131415'),
        ]);

        User::create([
            'name' => 'Betty Khoirun Nisa (COORDINATOR PRODUKSI)',
            'email' => 'knisabeti@gmail.com',
            'phone' => '628977222037',
            'role' => 'petinggi',
            'password' => Hash::make('Ospro2026'),
        ]);

        User::create([
            'name' => 'RISMALINDA FEBIARINI (COORDINATOR TRAINING DAN INKUBASI)',
            'email' => 'rismalindaf27@gmail.com',
            'phone' => '6283851350877',
            'role' => 'petinggi',
            'password' => Hash::make('RISMA998'),
        ]);

        User::create([
            'name' => 'Prasetya Eka Prabowo (Manager Pelatihan) ',
            'email' => 'prasetya@sigap.com',
            'phone' => '628999998060',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Halimah Madinatul Islam (COORDINATOR MARKETING)',
            'email' => 'denayabaksya@gmail.com',
            'phone' => '085113662112',
            'role' => 'petinggi',
            'password' => Hash::make('denayabaksya123'),
        ]);

        User::create([
            'name' => 'Alham Amirudin (Staf Legal)',
            'email' => 'amirudin@sigap.com',
            'phone' => '6281233583526',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'SEKAR AYU KINASIH (PIC KEUANGAN)',
            'email' => 'sekarworkie@gmail.com',
            'phone' => '085735302063',
            'role' => 'petinggi',
            'password' => Hash::make('191199'),
        ]);

        User::create([
            'name' => 'ADEK AYU AGUSTIN (ADMINISTRASI HRD)',
            'email' => 'spvagn@gmail.com',
            'phone' => '085117489835',
            'role' => 'petinggi',
            'password' => Hash::make('AdministrasiHrd123'),
        ]);

        User::create([
            'name' => 'APRILIA DEWI ANITA (STAF KEUANGAN)',
            'email' => 'apriliadewianita@gmail.com',
            'phone' => '087750141597',
            'role' => 'petinggi',
            'password' => Hash::make('Aprilia15!'),
        ]);

        User::create([
            'name' => 'DIAH RAGIL SAPUTRI (STAF KEUANGAN)',
            'email' => 'dideicassie@gmail.com',
            'phone' => '085853538125',
            'role' => 'petinggi',
            'password' => Hash::make('12345678'),
        ]);

        User::create([
            'name' => 'Yeni isnawati (First President)',
            'email' => 'yeniisnawati@sigap.com',
            'phone' => '6281331009455',
            'role' => 'petinggi',
            'password' => Hash::make('password'),
        ]);

        // 4. Akun Direksi
        User::create([
            'name' => 'Direksi',
            'email' => 'direksi@sigap.com',
            'phone' => '',
            'role' => 'dirut',
            'password' => Hash::make('password'),
        ]);
    }
}