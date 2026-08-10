<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Review SK (Petinggi)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <a href="/demo/aspri" class="bg-slate-800 hover:bg-slate-700 p-2 rounded-lg text-xs font-semibold text-slate-300 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h1 class="font-bold text-lg leading-none border-l border-slate-700 pl-3">SIGAP_V1 <span class="text-xs text-indigo-400 font-normal">/ Lembar Review SK</span></h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-amber-500/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-500/30">Role: Pak Budi (Validator Legal)</span>
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center font-bold">B</div>
        </div>
    </nav>

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Document Viewer (PDF Mockup) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[82vh]">
            <!-- Header Doc -->
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">SK Pengangkatan Tim Dev SIGAP 2026</h2>
                    <p class="text-xs text-slate-500">Versi: Draft_SK_v1.pdf • ADMINISTRATOR: ADMINISTRATOR Utama</p>
                </div>
                <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded">Versi 1</span>
            </div>

            <!-- PDF Visual Simulator -->
            <div class="flex-1 bg-slate-200 p-6 overflow-y-auto flex justify-center">
                <div class="bg-white w-full max-w-xl p-8 shadow-md rounded border border-slate-300 text-slate-700 text-xs space-y-4">
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
                    <div class="pt-8 flex justify-end">
                        <div class="text-center border-2 border-dashed border-slate-300 p-3 rounded w-40 text-[10px] text-slate-400">
                            [ Area TTD Dirut + QR Code Verifikasi ]
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Panel Komentar & Aksi Petinggi -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col h-[82vh]">
            <h3 class="font-bold text-slate-900 text-sm mb-3 flex items-center gap-2 border-b pb-3">
                <i class="fa-regular fa-comments text-indigo-600"></i> Diskusi & Komentar (Drive-Style)
            </h3>

            <!-- List Komentar -->
            <div class="flex-1 overflow-y-auto space-y-3 pr-1 text-xs">
                <!-- Komentar 1 (Pak Andi) -->
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-slate-800">Pak Andi (Keuangan)</span>
                        <span class="text-[10px] text-slate-400">10:15 WIB</span>
                    </div>
                    <p class="text-slate-600">Apakah pasal mengenai anggaran operasional sudah disesuaikan dengan laporan triwulan?</p>
                </div>

                <!-- Komentar 2 (User Ini - Pak Budi) -->
                <div class="bg-indigo-50/60 p-3 rounded-xl border border-indigo-200">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-indigo-900">Anda (Pak Budi - Legal)</span>
                        <span class="text-[10px] text-indigo-400">Baru saja</span>
                    </div>
                    <p class="text-indigo-800">Secara hukum redaksi pasal 1 sudah aman dan sesuai regulasi internal.</p>
                </div>
            </div>

            <!-- Input Komentar -->
            <div class="mt-3 pt-3 border-t border-slate-200">
                <div class="flex gap-1.5 mb-2 overflow-x-auto pb-1 text-[10px]">
                    <button onclick="document.getElementById('commentInput').value='Secara redaksi sudah sesuai.'" class="bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded text-slate-600 whitespace-nowrap">+ Quick Chat: "Redaksi Oke"</button>
                    <button onclick="document.getElementById('commentInput').value='Perlu revisi pada pasal...'" class="bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded text-slate-600 whitespace-nowrap">+ Quick Chat: "Minta Revisi"</button>
                </div>
                <div class="flex gap-2">
                    <input id="commentInput" type="text" placeholder="Tulis catatan revisi/komentar..." class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-xs outline-none focus:ring-2 focus:ring-indigo-500">
                    <button onclick="alert('Komentar terposting! Seluruh validator & ADMINISTRATOR dapat melihatnya.')" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-xs font-semibold hover:bg-indigo-700">Kirim</button>
                </div>
            </div>

            <!-- Panel Tombol Aksi Kunci -->
            <div class="mt-4 pt-4 border-t border-slate-200 space-y-2">
                <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Berikan Keputusan Anda:</p>
                <div class="grid grid-cols-3 gap-2">
                    <button onclick="alert('Demo: Anda memilih TERIMA. SK berlanjut ke pimpinan berikutnya/Dirut.')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg text-xs transition shadow-sm">
                        <i class="fa-solid fa-check mr-1"></i> TERIMA
                    </button>
                    <button onclick="alert('Demo: Anda memilih PENDING. Status dokumen berubah menjadi Minta Revisi ke Aspri.')" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-lg text-xs transition shadow-sm">
                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> PENDING
                    </button>
                    <button onclick="alert('Demo: Anda memilih TOLAK. Proses pengajuan SK ini dihentikan (HARD STOP).')" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 rounded-lg text-xs transition shadow-sm">
                        <i class="fa-solid fa-xmark mr-1"></i> TOLAK
                    </button>
                </div>
            </div>

        </div>

    </div>

</body>
</html>