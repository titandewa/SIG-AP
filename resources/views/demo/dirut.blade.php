<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Dashboard Dirut & Approval TTD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-indigo-950 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <a href="/demo/petinggi" class="bg-indigo-900 hover:bg-indigo-800 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali Ke Petinggi
            </a>
            <h1 class="font-bold text-lg leading-none border-l border-indigo-800 pl-3">
                SIGAP_V1 <span class="text-xs text-amber-400 font-normal">/ Executive Dashboard</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-amber-400/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-400/30 font-semibold">
                Role: Direktur Utama (Dirut)
            </span>
            <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-900 flex items-center justify-center font-extrabold">D</div>
        </div>
    </nav>

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Document Preview & Auto-Stamp Demo -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[82vh]">
            <!-- Header Doc -->
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">SK Pengangkatan Tim Dev SIGAP 2026</h2>
                    <p class="text-xs text-slate-500">Nomor: 042/SK-DIR/SIGAP/2026 • Status: <span class="text-emerald-600 font-bold">100% Disetujui Petinggi</span></p>
                </div>
                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                    <i class="fa-solid fa-shield-check"></i> Siap TTD
                </span>
            </div>

            <!-- PDF Visual Simulator dengan Live Auto-Stamp -->
            <div class="flex-1 bg-slate-200 p-6 overflow-y-auto flex justify-center">
                <div class="bg-white w-full max-w-xl p-8 shadow-md rounded border border-slate-300 text-slate-700 text-xs space-y-4 relative">
                    
                    <!-- Watermark Draft / Signed State -->
                    <div id="signedBadge" class="hidden absolute top-12 right-12 border-4 border-emerald-600 text-emerald-600 font-extrabold text-lg px-4 py-1 rounded-lg rotate-12 opacity-80 uppercase tracking-widest">
                        DISETUJU & SAH
                    </div>

                    <div class="text-center border-b pb-4 mb-4">
                        <h3 class="font-bold text-base uppercase text-slate-900">SURAT KEPUTUSAN</h3>
                        <p class="text-[10px] text-slate-500">Nomor: 042/SK-DIR/SIGAP/2026</p>
                    </div>
                    <p class="font-semibold">MEMUTUSKAN:</p>
                    <p class="text-justify leading-relaxed">
                        Menetapkan tim pengembang software SIGAP_V1 untuk melaksanakan digitalisasi alur pengajuan dokumen internal dengan berlandaskan transparansi dan keamanan...
                    </p>
                    <div class="bg-indigo-50/50 p-3 rounded border border-indigo-100 text-[11px]">
                        <strong>Pasal 1:</strong> Hak dan kewajiban pengajuan dokumen diatur secara transparan.
                    </div>
                    <p class="text-justify leading-relaxed">
                        Demikian surat keputusan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
                    </p>

                    <!-- Target Area TTD Dirut -->
                    <div class="pt-8 flex justify-center">
                        <div id="ttdContainer" class="text-center border-2 border-dashed border-slate-300 p-4 rounded-xl w-64 bg-slate-50 transition-all duration-500">
                            <p class="text-[10px] text-slate-400 mb-2">[ Area TTD Digital & QR Code ]</p>
                            <span class="text-[9px] text-slate-400 italic">Tekan 'TERIMA & TTD' di sebelah kanan untuk stempel otomatis.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Panel Keputusan Dirut & Verifikasi Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between h-[82vh]">
            
            <div class="space-y-4">
                <h3 class="font-bold text-slate-900 text-sm border-b pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-signature text-amber-500"></i> Lembar Persetujuan Akhir
                </h3>

                <!-- Status Approval Petinggi -->
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs space-y-2">
                    <p class="font-bold text-slate-700">Ringkasan Persetujuan Petinggi:</p>
                    <div class="flex items-center justify-between text-emerald-700 font-semibold bg-white p-2 rounded border border-emerald-200">
                        <span>1. Pak Budi (Legal)</span>
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="flex items-center justify-between text-emerald-700 font-semibold bg-white p-2 rounded border border-emerald-200">
                        <span>2. Pak Andi (Keuangan)</span>
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>

                <!-- Opsi Posisi TTD (Fitur Fleksibel yang Kamu Request) -->
                <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100 text-xs">
                    <label class="block font-bold text-indigo-950 mb-1.5">Posisi Penempelan TTD:</label>
                    <select class="w-full p-2 bg-white border border-indigo-200 rounded-lg text-xs outline-none">
                        <option value="center">Halaman Terakhir - Tengah Bawah (Default)</option>
                        <option value="right">Halaman Terakhir - Kanan Bawah</option>
                        <option value="left">Halaman Terakhir - Kiri Bawah</option>
                    </select>
                </div>
            </div>

            <!-- Panel Action Button Dirut -->
            <div class="space-y-3 pt-4 border-t border-slate-200">
                <button onclick="eksekusiTTD()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-xl text-sm transition shadow-lg shadow-emerald-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-stamp text-lg"></i> TERIMA & TTD DOKUMEN
                </button>

                <div class="grid grid-cols-2 gap-2">
                    <button onclick="alert('Demo: Dirut meminta revisi minor sebelum TTD.')" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 rounded-lg text-xs transition">
                        PENDING / REVISI
                    </button>
                    <button onclick="alert('Demo: Dirut menolak pengajuan SK ini.')" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 rounded-lg text-xs transition">
                        TOLAK
                    </button>
                </div>

                <!-- Tombol Cek Verifikasi Publik -->
                <a id="btnVerify" href="/demo/verify" target="_blank" class="hidden w-full bg-slate-900 hover:bg-slate-800 text-amber-400 font-bold py-2.5 rounded-xl text-xs text-center block transition border border-slate-700">
                    <i class="fa-solid fa-qrcode mr-1"></i> Simulasi Scan QR Verifikasi Publik
                </a>
            </div>

        </div>

    </div>

    <!-- Script Simulasi Auto-Stamp TTD + QR Code -->
    <script>
        function eksekusiTTD() {
            const container = document.getElementById('ttdContainer');
            const badge = document.getElementById('signedBadge');
            const btnVerify = document.getElementById('btnVerify');

            // Render TTD Visual
            container.className = "text-center border-2 border-emerald-500 p-3 rounded-xl w-64 bg-emerald-50/50 shadow-md transition-all duration-500";
            container.innerHTML = `
                <p class="text-[9px] text-slate-500 font-semibold mb-1">Ditandatangani secara Digital oleh:</p>
                <div class="my-1 py-1 font-serif text-lg font-bold text-slate-900 tracking-widest border-y border-emerald-300 italic">
                    Direktur Utama
                </div>
                <div class="flex justify-center items-center gap-2 mt-2 bg-white p-1.5 rounded border border-emerald-200">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=http://127.0.0.1:8000/demo/verify" alt="QR Code" class="w-12 h-12">
                    <div class="text-[8px] text-left text-slate-600 leading-tight">
                        <strong class="text-emerald-700">SK SAH & TERVERIFIKASI</strong><br>
                        ID: SIGAP-2026-042<br>
                        Tgl: 23 Juli 2026
                    </div>
                </div>
            `;

            // Tampilkan Watermark & Tombol Verifikasi
            badge.classList.remove('hidden');
            btnVerify.classList.remove('hidden');

            alert('BERHASIL! SK telah ditandatangani secara digital. TTD + QR Code otomatis tertempel pada halaman dokumen.');
        }
    </script>

</body>
</html>