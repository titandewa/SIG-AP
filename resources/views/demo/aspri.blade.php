<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Dashboard Aspri (Demo)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 p-2 rounded-lg font-bold text-xl">S</div>
            <div>
                <h1 class="font-bold text-lg leading-none">SIGAP_V1</h1>
                <span class="text-xs text-indigo-200">Sistem Pengajuan & Approval SK</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-indigo-800 text-xs px-3 py-1 rounded-full border border-indigo-700">Role: Aspri / Admin</span>
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center font-bold">A</div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Form Upload SK -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 h-fit">
            <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-file-circle-plus text-indigo-600"></i> Buat Pengajuan SK Baru
            </h2>
            
            <form onsubmit="alert('Demo: SK Berhasil Diunggah & Notifikasi WA Terkirim ke Petinggi!'); return false;" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Judul / Perihal SK</label>
                    <input type="text" value="SK Pengangkatan Tim Dev SIGAP 2026" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Upload Berkas SK (.pdf)</label>
                    <div class="border-2 border-dashed border-slate-300 p-4 rounded-xl text-center hover:border-indigo-500 cursor-pointer bg-slate-50">
                        <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-1"></i>
                        <p class="text-xs text-slate-500 font-medium">Klik untuk upload file SK (.pdf)</p>
                        <span class="text-[10px] text-slate-400">Draft_SK_Utama.pdf (1.2 MB)</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-2">Pilih Petinggi Peninjau (Reviewer)</label>
                    <div class="space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="rounded text-indigo-600">
                            <span>Pak Budi (Petinggi Legal / Hukum)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" checked class="rounded text-indigo-600">
                            <span>Pak Andi (Petinggi Keuangan)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="rounded text-indigo-600">
                            <span>Bu Citra (Petinggi SDM/HRD)</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-md shadow-indigo-200">
                    <i class="fa-solid fa-paper-plane mr-1"></i> Ajukan & Kirim WA Notif
                </button>
            </form>
        </div>

        <!-- Kolom Kanan: Daftar Pengajuan SK & Auto WA Reminder -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Daftar Pengajuan SK Aktif</h2>
                    <p class="text-xs text-slate-500">Pantau proses persetujuan dan kirim reminder otomatis.</p>
                </div>
                <span class="bg-indigo-50 text-indigo-700 font-bold text-xs px-3 py-1 rounded-full">Total: 1 SK</span>
            </div>

            <!-- Card Items Pengajuan -->
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50 hover:bg-white transition">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="bg-amber-100 text-amber-800 font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            <i class="fa-solid fa-spinner animate-spin mr-1"></i> DITINJAU
                        </span>
                        <h3 class="font-bold text-slate-800 text-base mt-1">SK Pengangkatan Tim Dev SIGAP 2026</h3>
                        <p class="text-xs text-slate-500">Diajukan: 23 Juli 2026 • File: Draft_SK_v1.pdf</p>
                    </div>
                    
                    <!-- Tombol Magic WA Reminder -->
                    <button onclick="alert('Demo: Auto-WA Reminder Terkirim HANYA ke Pak Andi (karena Pak Andi belum merespons). Pak Budi diskip!')" 
                            class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp text-sm"></i> Send Auto-WA Reminder
                    </button>
                </div>

                <hr class="my-3 border-slate-200">

                <!-- Tracking Petinggi -->
                <div class="text-xs">
                    <p class="font-semibold text-slate-700 mb-2">Status Persetujuan Petinggi:</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <!-- Petinggi 1: Sudah Setuju -->
                        <div class="flex items-center justify-between bg-white p-2.5 rounded-lg border border-slate-200">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <div>
                                    <p class="font-semibold text-slate-800">Pak Budi (Legal)</p>
                                    <p class="text-[10px] text-slate-400">Menyetujui pada v1</p>
                                </div>
                            </div>
                            <span class="bg-emerald-50 text-emerald-700 font-bold text-[10px] px-2 py-0.5 rounded">TERIMA</span>
                        </div>

                        <!-- Petinggi 2: Belum Respon -->
                        <div class="flex items-center justify-between bg-white p-2.5 rounded-lg border border-amber-300 bg-amber-50/30">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                                <div>
                                    <p class="font-semibold text-slate-800">Pak Andi (Keuangan)</p>
                                    <p class="text-[10px] text-amber-600 font-medium">Belum Menanggapi</p>
                                </div>
                            </div>
                            <span class="bg-amber-100 text-amber-800 font-bold text-[10px] px-2 py-0.5 rounded">MENUNGGU</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Card / Akses Petinggi Demo Navigation -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                    <span class="text-slate-400 italic">*Petinggi lain dapat melihat komentar secara transparan.</span>
                    <a href="/demo/petinggi" class="text-indigo-600 font-bold hover:underline flex items-center gap-1">
                        Simulasi Login sebagai Petinggi <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>