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
            <a href="{{ route('app.petinggi.dashboard') }}" class="bg-indigo-900 hover:bg-indigo-800 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali Ke Petinggi
            </a>
            <h1 class="font-bold text-lg leading-none border-l border-indigo-800 pl-3">
                SIGAP_V1 <span class="text-xs text-amber-400 font-normal">/ Executive Dashboard</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-amber-400/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-400/30 font-semibold">
                Role: {{ auth()->user()->name ?? 'Direktur Utama (Dirut)' }}
            </span>
            <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-900 flex items-center justify-center font-extrabold">
                {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="bg-rose-50 border border-rose-300 text-rose-800 text-sm px-4 py-3 rounded-lg">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Document Preview & Auto-Stamp Demo -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[82vh]">
            <!-- Header Doc -->
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">
                        {{ $readyToSignDocuments->first()->title ?? 'Belum ada dokumen' }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        Nomor: {{ $readyToSignDocuments->first()->document_number ?? '-' }} • Status:
                        <span class="text-emerald-600 font-bold">100% Disetujui Petinggi</span>
                    </p>
                </div>
                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                    <i class="fa-solid fa-shield-check"></i> Siap TTD
                </span>
            </div>

            <!-- PDF Real Preview dengan Live Auto-Stamp Overlay -->
            <div class="flex-1 bg-slate-200 overflow-hidden relative flex justify-center">
                @if($readyToSignDocuments->first() && $readyToSignDocuments->first()->latestVersion)
                    <iframe
                        src="{{ asset('storage/' . $readyToSignDocuments->first()->latestVersion->file_path) }}"
                        class="w-full h-full border-0"
                        title="Preview Dokumen SK">
                    </iframe>

                    <!-- Watermark Overlay Disetujui & Sah -->
                    <div id="signedBadge" class="hidden absolute top-8 right-8 border-4 border-emerald-600 text-emerald-600 font-extrabold text-lg px-4 py-1 rounded-lg rotate-12 opacity-80 uppercase tracking-widest bg-white/70 pointer-events-none">
                        DISETUJUI & SAH
                    </div>

                    <!-- Overlay Stempel TTD Digital -->
                    <div id="positionWrapper" class="hidden absolute bottom-8 left-8 right-8 flex justify-center pointer-events-none">
                        <div id="ttdContainer" class="text-center border-2 border-emerald-500 p-3 rounded-xl w-64 bg-emerald-50/90 shadow-md transition-all duration-500">
                            <p class="text-[9px] text-slate-500 font-semibold mb-1">Ditandatangani secara Digital oleh:</p>
                            <div class="my-1 py-1 font-serif text-lg font-bold text-slate-900 tracking-widest border-y border-emerald-300 italic">
                                Direktur Utama
                            </div>
                            <div class="flex justify-center items-center gap-2 mt-2 bg-white p-1.5 rounded border border-emerald-200">
                                <img id="qrImg" src="" alt="QR Code" class="w-12 h-12">
                                <div class="text-[8px] text-left text-slate-600 leading-tight">
                                    <strong class="text-emerald-700">SK SAH & TERVERIFIKASI</strong><br>
                                    <span id="qrDocId"></span><br>
                                    Tgl: {{ now()->translatedFormat('d F Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-center h-full text-slate-400 text-sm">
                        <i class="fa-solid fa-file-circle-xmark text-3xl mr-2"></i> Tidak ada dokumen yang siap ditandatangani.
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Panel Keputusan Dirut & Dynamic List -->
        <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 p-5 flex flex-col h-[82vh] overflow-y-auto">
            
            <div class="space-y-4">
                <h3 class="font-bold text-white text-sm border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-signature text-amber-500"></i> Dokumen Siap Disahkan
                </h3>

                <!-- Opsi Posisi TTD -->
                <div class="bg-slate-800 p-3 rounded-xl border border-slate-700 text-xs">
                    <label class="block font-bold text-slate-300 mb-1.5">Posisi Penempelan Stempel TTD:</label>
                    <select id="stampPosition" onchange="updateStampPosition(this.value)" class="w-full p-2 bg-slate-900 text-white border border-slate-700 rounded-lg text-xs outline-none focus:border-indigo-500">
                        <option value="center">Halaman Terakhir - Tengah Bawah (Default)</option>
                        <option value="right">Halaman Terakhir - Kanan Bawah</option>
                        <option value="left">Halaman Terakhir - Kiri Bawah</option>
                    </select>
                </div>

                <!-- List Dokumen Siap TTD (Dynamic Loop) -->
                <div class="space-y-4">
                    @forelse($readyToSignDocuments as $doc)
                        <div class="p-4 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3">
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $doc->title }}</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">No: {{ $doc->document_number }}</p>
                                <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Persetujuan Petinggi Lengkap (100%)</span>
                                </div>
                            </div>

                            <!-- Form Sahkan & TTD Dirut -->
                            <form action="{{ route('dirut.sign', $doc->id) }}" method="POST" onsubmit="eksekusiTTDLocal(event, '{{ $doc->document_number }}')">
                                @csrf
                                <input type="hidden" name="position" id="formPositionInput_{{ $doc->id }}" value="center">
                                
                                <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-md shadow-indigo-900/50">
                                    <i class="fa-solid fa-stamp"></i> Sahkan & TTD Digital
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-slate-800">
                            <i class="fa-solid fa-file-circle-xmark text-3xl text-slate-600 mb-2 block"></i>
                            <p class="text-slate-400 text-xs">Belum ada dokumen yang siap ditandatangani saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Tombol Verifikasi Publik -->
            <div class="mt-auto pt-4 border-t border-slate-800">
                <a id="btnVerify" href="#" target="_blank" class="hidden w-full bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold py-2.5 rounded-xl text-xs text-center block transition border border-slate-700">
                    <i class="fa-solid fa-qrcode mr-1"></i> Simulasi Scan QR Verifikasi Publik
                </a>
            </div>

        </div>

    </div>

    <!-- Script Handling Posisi & Visual Auto-Stamp -->
    <script>
        function updateStampPosition(pos) {
            const wrapper = document.getElementById('positionWrapper');
            document.querySelectorAll('input[name="position"]').forEach(input => {
                input.value = pos;
            });

            if (!wrapper) return;

            if (pos === 'left') {
                wrapper.className = wrapper.className.replace(/justify-\S+/, 'justify-start');
            } else if (pos === 'right') {
                wrapper.className = wrapper.className.replace(/justify-\S+/, 'justify-end');
            } else {
                wrapper.className = wrapper.className.replace(/justify-\S+/, 'justify-center');
            }
        }

        function eksekusiTTDLocal(e, docNumber) {
            const badge = document.getElementById('signedBadge');
            const wrapper = document.getElementById('positionWrapper');
            const btnVerify = document.getElementById('btnVerify');
            const qrImg = document.getElementById('qrImg');
            const qrDocId = document.getElementById('qrDocId');

            if (badge) badge.classList.remove('hidden');
            if (wrapper) wrapper.classList.remove('hidden');
            if (qrDocId) qrDocId.textContent = 'No: ' + docNumber;
            if (qrImg) qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=' + encodeURIComponent(docNumber);
            if (btnVerify) btnVerify.classList.remove('hidden');
        }
    </script>

</body>
</html>