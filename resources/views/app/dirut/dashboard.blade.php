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
            <a href="{{ route('dirut.riwayat') }}" class="bg-indigo-900 hover:bg-indigo-800 text-xs px-3 py-1.5 rounded-lg border border-indigo-700 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
            </a>
            <a href="{{ route('dirut.signature.page') }}" class="bg-indigo-900 hover:bg-indigo-800 text-xs px-3 py-1.5 rounded-lg border border-indigo-700 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-signature"></i> Atur TTD
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
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

        <!-- Kolom Kiri: Document Preview (mengikuti dokumen yang dipilih di kanan) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[82vh]">
            <!-- Header Doc -->
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                <div>
                    <h2 id="previewTitle" class="font-bold text-slate-900 text-sm">
                        {{ $readyToSignDocuments->first()->title ?? 'Belum ada dokumen' }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        Nomor: <span id="previewNumber">{{ $readyToSignDocuments->first()->document_number ?? '-' }}</span> • Status:
                        <span class="text-emerald-600 font-bold">100% Disetujui Petinggi</span>
                    </p>
                </div>
                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1 shrink-0">
                    <i class="fa-solid fa-shield-check"></i> Siap TTD
                </span>
            </div>

            <!-- PDF Preview -->
            <div class="flex-1 bg-slate-200 overflow-hidden relative flex justify-center">
                @if($readyToSignDocuments->first() && $readyToSignDocuments->first()->latestVersion)
                    <iframe
                        id="previewFrame"
                        src="{{ asset('storage/' . $readyToSignDocuments->first()->latestVersion->file_path) }}"
                        class="w-full h-full border-0"
                        title="Preview Dokumen SK">
                    </iframe>
                @else
                    <div id="previewEmpty" class="flex items-center justify-center h-full text-slate-400 text-sm">
                        <i class="fa-solid fa-file-circle-xmark text-3xl mr-2"></i> Tidak ada dokumen yang siap ditandatangani.
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: List Dokumen Siap TTD -->
        <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 p-5 flex flex-col h-[82vh] overflow-y-auto">

            <div class="space-y-4">
                <h3 class="font-bold text-white text-sm border-b border-slate-800 pb-3 flex items-center gap-2">
                    <i class="fa-solid fa-file-signature text-amber-500"></i> Dokumen Siap Disahkan
                </h3>

                <!-- List Dokumen Siap TTD (Dynamic Loop, klik untuk preview) -->
                <div class="space-y-4">
                    @forelse($readyToSignDocuments as $doc)
                        <div class="doc-card p-4 bg-slate-950/60 border rounded-xl space-y-3 cursor-pointer transition {{ $loop->first ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-slate-800' }}"
                            data-id="{{ $doc->id }}"
                            onclick="selectDocument({{ $doc->id }})">
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $doc->title }}</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">No: {{ $doc->document_number }}</p>
                                <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Persetujuan Petinggi Lengkap (100%)</span>
                                </div>
                            </div>

                            <!-- Lanjut ke editor untuk drag & drop TTD -->
                            <a href="{{ route('dirut.ttd.editor', $doc->id) }}" onclick="event.stopPropagation()"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-md shadow-indigo-900/50">
                                <i class="fa-solid fa-stamp"></i> Sahkan & TTD Digital
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-slate-800">
                            <i class="fa-solid fa-file-circle-xmark text-3xl text-slate-600 mb-2 block"></i>
                            <p class="text-slate-400 text-xs">Belum ada dokumen yang siap ditandatangani saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    @php
        $readyDocumentsForJs = [];
        foreach ($readyToSignDocuments as $d) {
            $readyDocumentsForJs[] = [
                'id' => $d->id,
                'title' => $d->title,
                'document_number' => $d->document_number,
                'file_url' => $d->latestVersion ? asset('storage/' . $d->latestVersion->file_path) : null,
            ];
        }
    @endphp

    <!-- Script: switch preview kiri sesuai dokumen yang diklik -->
    <script>
        const readyDocuments = {!! json_encode($readyDocumentsForJs) !!};

        function selectDocument(id) {
            const doc = readyDocuments.find(d => d.id === id);
            if (!doc) return;

            const titleEl = document.getElementById('previewTitle');
            const numberEl = document.getElementById('previewNumber');
            if (titleEl) titleEl.textContent = doc.title;
            if (numberEl) numberEl.textContent = doc.document_number;

            const iframe = document.getElementById('previewFrame');
            if (iframe && doc.file_url) {
                iframe.src = doc.file_url;
            }

            document.querySelectorAll('.doc-card').forEach(card => {
                const active = parseInt(card.dataset.id) === id;
                card.classList.toggle('border-indigo-500', active);
                card.classList.toggle('ring-1', active);
                card.classList.toggle('ring-indigo-500', active);
                card.classList.toggle('border-slate-800', !active);
            });
        }
    </script>

</body>
</html>