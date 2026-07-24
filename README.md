# SIGAP_V1

Sistem Pengajuan & Approval SK (Surat Keputusan) berbasis web — mendigitalisasi alur pengajuan, review, hingga penandatanganan dokumen internal secara transparan dan terlacak.

> Status: **MVP (v0.1.0)** — alur utama sudah berjalan, beberapa fitur pendukung (notifikasi WA, verifikasi QR publik) masih dalam pengembangan.

## Latar Belakang

Proses pengajuan SK di banyak instansi masih manual: dokumen fisik berpindah tangan, sulit dilacak siapa yang sudah/belum menyetujui, dan rawan miskomunikasi. SIGAP_V1 mendigitalisasi alur ini dengan sistem approval berjenjang dan riwayat komentar yang transparan antar peninjau.

## Alur Sistem

```
Aspri (Pengaju)
   │  upload SK + pilih Petinggi peninjau
   ▼
Petinggi (Reviewer, bisa lebih dari satu)
   │  Setujui / Revisi / Tolak + komentar
   │  (komentar terlihat oleh semua Petinggi lain)
   ▼
Jika SEMUA Petinggi menyetujui → status "Siap TTD"
   ▼
Dirut (Direktur Utama)
   │  Sahkan & Tanda Tangan Digital
   ▼
Dokumen Disetujui & Sah
```

## Role & Hak Akses

| Role | Deskripsi | Akses Utama |
|---|---|---|
| **Aspri** | Mengajukan SK baru | Upload dokumen, pilih peninjau, pantau status |
| **Petinggi** | Peninjau/reviewer | Setujui, minta revisi, atau tolak + beri komentar |
| **Dirut** | Direktur Utama | Menandatangani dokumen yang sudah disetujui 100% |

Setiap role dibatasi lewat middleware (`role:aspri`, `role:petinggi`, `role:dirut`) sehingga hanya bisa mengakses dashboard sesuai perannya.

## Tech Stack

- **Backend:** Laravel
- **Database:** MySQL
- **Frontend:** Blade Template + Tailwind CSS
- **Local Dev Environment:** Laragon

## Fitur Utama

- Upload & penyimpanan berkas SK (PDF)
- Preview dokumen langsung di browser
- Approval berjenjang dengan status: `DITINJAU`, `PENDING`, `SIAP_TTD`, `DISETUJUI`, `DITOLAK`
- Riwayat komentar transparan antar Petinggi
- Nomor dokumen digenerate otomatis (mencegah human error)
- Tanda tangan digital oleh Dirut dengan posisi stempel yang bisa diatur
- Drag & drop upload dengan indikator progres

## Roadmap

- [ ] Perbaikan potensi race condition pada penomoran dokumen otomatis
- [ ] Alur revisi dokumen (re-upload setelah status Revisi/Pending)
- [ ] Integrasi notifikasi WhatsApp otomatis ke Petinggi yang belum merespons
- [ ] Verifikasi dokumen publik via QR Code
- [ ] Automated testing untuk alur approval

## Instalasi Lokal

```bash
git clone https://github.com/username-kamu/sigap-v1.git
cd sigap-v1

composer install
npm install

cp .env.example .env
php artisan key:generate

# Sesuaikan koneksi database di file .env
php artisan migrate

php artisan storage:link

npm run dev
php artisan serve
```

## Struktur Direktori Penting

```
app/Http/Controllers/DocumentController.php   # Logika utama alur dokumen
app/Models/                                    # Document, DocumentApproval, DocumentVersion, Comment
resources/views/app/aspri/dashboard.blade.php     # Dashboard Aspri
resources/views/app/petinggi/dashboard.blade.php  # Dashboard Petinggi
resources/views/app/dirut/dashboard.blade.php     # Dashboard Dirut
routes/web.php                                 # Definisi route per role
```

## Kontribusi

Proyek ini masih dalam pengembangan aktif sebagai bagian dari pembelajaran/portofolio pribadi. Saran dan masukan terbuka lewat Issues.

---

*Dibangun sebagai eksplorasi pengembangan sistem approval dokumen digital berbasis Laravel.*