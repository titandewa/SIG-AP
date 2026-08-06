<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Dashboard Dirut & Approval TTD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        html, body { height: 100%; }
        #docListSidebar::-webkit-scrollbar { width: 8px; }
        #docListSidebar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
    @include('partials.fcm-notif')
</head>
<body class="bg-slate-100 font-sans text-slate-800 lg:h-full">

<div class="flex flex-col lg:h-full">
    <!-- Topbar -->
    <nav class="bg-indigo-950 text-white px-4 sm:px-6 py-3 flex justify-between items-center shadow-md shrink-0 z-20 relative sticky top-0">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <button onclick="togglePanel('list')" class="lg:hidden bg-indigo-900 hover:bg-indigo-800 p-2 rounded-lg text-xs text-indigo-200 transition shrink-0">
                <i class="fa-solid fa-list"></i>
            </button>
            <a href="{{ route('app.petinggi.dashboard') }}" class="bg-indigo-900 hover:bg-indigo-800 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition shrink-0">
                <i class="fa-solid fa-arrow-left"></i> <span class="hidden sm:inline">Kembali</span>
            </a>
            <h1 class="font-bold text-sm sm:text-lg leading-none border-l border-indigo-800 pl-3 truncate">
                SIGAP_V1 <span class="text-xs text-amber-400 font-normal hidden sm:inline">/ Executive Dashboard</span>
            </h1>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-900 flex items-center justify-center font-extrabold shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
            </div>
            <a href="{{ route('dirut.riwayat') }}" class="bg-indigo-900 hover:bg-indigo-800 text-xs px-3 py-2 rounded-lg border border-indigo-700 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-clock-rotate-left"></i> <span class="hidden sm:inline">Riwayat</span>
            </a>
            <a href="{{ route('dirut.signature.page') }}" class="bg-indigo-900 hover:bg-indigo-800 text-xs px-3 py-2 rounded-lg border border-indigo-700 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-signature"></i> <span class="hidden sm:inline">Atur TTD</span>
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    @if(session('success'))
        <div class="px-4 pt-3 shrink-0">
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="px-4 pt-3 shrink-0">
            <div class="bg-rose-50 border border-rose-300 text-rose-800 text-sm px-4 py-3 rounded-lg">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Backdrop mobile (hanya untuk drawer list dokumen) -->
    <div id="mobileBackdrop" onclick="closeAllPanels()" class="hidden fixed inset-0 bg-slate-900/50 z-20 lg:hidden"></div>

    <div class="flex flex-col lg:flex-row flex-1 min-h-0 relative">

        <!-- Sidebar Kiri: List Dokumen (drawer di HP) -->
        <aside id="docListSidebar"
            class="hidden lg:flex lg:flex-col fixed lg:static inset-y-0 left-0 z-30 w-72 bg-slate-900 border-r border-slate-800 shrink-0 shadow-xl lg:shadow-none">
            <div class="flex justify-between items-center p-4 border-b border-slate-800 lg:hidden">
                <span class="text-white font-bold text-sm">Dokumen Siap Disahkan</span>
                <button onclick="closeAllPanels()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                <p class="hidden lg:block text-xs font-bold text-slate-400 uppercase px-1 mb-1">
                    <i class="fa-solid fa-file-signature text-amber-500 mr-1"></i> Dokumen Siap Disahkan
                </p>
                @forelse($readyToSignDocuments as $doc)
                    <button onclick="selectDocument({{ $doc->id }})" id="listitem-{{ $doc->id }}"
                        class="doc-list-item w-full text-left p-3 rounded-xl border transition
                        {{ $loop->first ? 'bg-indigo-600/20 border-indigo-500' : 'bg-slate-950/50 border-slate-800 hover:border-slate-700' }}">
                        <p class="font-semibold text-white text-xs leading-snug line-clamp-2">{{ $doc->title }}</p>
                        <p class="text-[10px] text-slate-500 mt-1">No: {{ $doc->document_number }}</p>
                        <div class="mt-1.5 flex items-center gap-1.5 text-[10px] text-emerald-400 font-medium">
                            <i class="fa-solid fa-circle-check"></i> 100% Disetujui Petinggi
                        </div>
                    </button>
                @empty
                    <div class="text-center py-10">
                        <i class="fa-solid fa-file-circle-xmark text-3xl text-slate-700 mb-2 block"></i>
                        <p class="text-slate-500 text-xs">Belum ada dokumen yang siap ditandatangani.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- Tengah: Preview PDF -->
        <main class="flex flex-col min-w-0 bg-white lg:flex-1">
            <div class="p-3 sm:p-4 border-b border-slate-200 flex justify-between items-center gap-3 shrink-0 flex-wrap">
                <div class="min-w-0">
                    <h2 id="previewTitle" class="font-bold text-slate-900 text-sm truncate">
                        {{ $readyToSignDocuments->first()->title ?? 'Belum ada dokumen' }}
                    </h2>
                    <p class="text-xs text-slate-500 truncate">
                        No: <span id="previewNumber">{{ $readyToSignDocuments->first()->document_number ?? '-' }}</span> •
                        <span class="text-emerald-600 font-semibold">100% Disetujui Petinggi</span>
                    </p>
                </div>
                @if($readyToSignDocuments->first() && $readyToSignDocuments->first()->latestVersion)
                    <div class="flex items-center gap-2 shrink-0">
                        <button id="prevPageBtn" onclick="changePreviewPage(-1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                            <i class="fa-solid fa-chevron-up"></i>
                        </button>
                        <span id="previewPageInfo" class="text-xs text-slate-500 min-w-[50px] text-center">- / -</span>
                        <button id="nextPageBtn" onclick="changePreviewPage(1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                @endif
            </div>

            <div id="previewViewport" class="h-[55vh] lg:flex-1 lg:h-auto lg:min-h-0 bg-slate-200 flex justify-center items-center p-3 sm:p-6 overflow-hidden">
                @if($readyToSignDocuments->first() && $readyToSignDocuments->first()->latestVersion)
                    <canvas id="previewCanvas" class="shadow-md bg-white"></canvas>
                @else
                    <div class="text-slate-400 text-sm text-center">
                        <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i> Tidak ada dokumen yang siap ditandatangani.
                    </div>
                @endif
            </div>
        </main>

        <!-- Kanan: Aksi Sahkan & TTD -- SELALU TAMPIL, di bawah preview pada HP -->
        <aside id="actionPanel" class="flex flex-col bg-slate-900 border-t lg:border-t-0 lg:border-l border-slate-800 shrink-0 lg:w-80 p-4">
            @if($readyToSignDocuments->isEmpty())
                <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-slate-800">
                    <i class="fa-solid fa-file-circle-xmark text-3xl text-slate-600 mb-2 block"></i>
                    <p class="text-slate-400 text-xs">Belum ada dokumen yang siap ditandatangani saat ini.</p>
                </div>
            @else
                @foreach($readyToSignDocuments as $doc)
                    <div id="panel-{{ $doc->id }}" class="doc-panel {{ $loop->first ? '' : 'hidden' }}">
                        <div class="p-4 bg-slate-950/60 border border-slate-800 rounded-xl space-y-3">
                            <div>
                                <p class="text-[11px] text-slate-400">No: {{ $doc->document_number }}</p>
                                <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-400 font-medium">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span>Persetujuan Petinggi Lengkap (100%)</span>
                                </div>
                            </div>

                            <a href="{{ route('dirut.ttd.editor', $doc->id) }}"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-md shadow-indigo-900/50">
                                <i class="fa-solid fa-stamp"></i> Sahkan & TTD Digital
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </aside>
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

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const readyDocuments = {!! json_encode($readyDocumentsForJs) !!};
    let previewPdfDoc = null;
    let previewCurrentPage = 1;
    let previewTotalPages = 0;

    async function loadPreviewPdf(url) {
        if (!url) return;
        try {
            const response = await fetch(url);
            const bytes = await response.arrayBuffer();
            previewPdfDoc = await pdfjsLib.getDocument({ data: bytes }).promise;
            previewTotalPages = previewPdfDoc.numPages;
            previewCurrentPage = 1;
            await renderPreviewPage();
        } catch (e) {
            console.error('Gagal memuat PDF preview:', e);
        }
    }

    async function renderPreviewPage() {
        if (!previewPdfDoc) return;
        const page = await previewPdfDoc.getPage(previewCurrentPage);
        const rawViewport = page.getViewport({ scale: 1 });

        const container = document.getElementById('previewViewport');
        const availableWidth = container.clientWidth - 24;
        const availableHeight = container.clientHeight - 24;
        const scale = Math.max(Math.min(availableWidth / rawViewport.width, availableHeight / rawViewport.height), 0.1);
        const viewport = page.getViewport({ scale });
        const pixelRatio = window.devicePixelRatio || 1;

        const canvas = document.getElementById('previewCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        canvas.width = viewport.width * pixelRatio;
        canvas.height = viewport.height * pixelRatio;
        canvas.style.width = viewport.width + 'px';
        canvas.style.height = viewport.height + 'px';
        ctx.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

        await page.render({ canvasContext: ctx, viewport }).promise;

        const pageInfo = document.getElementById('previewPageInfo');
        if (pageInfo) pageInfo.textContent = previewCurrentPage + ' / ' + previewTotalPages;
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        if (prevBtn) prevBtn.disabled = previewCurrentPage <= 1;
        if (nextBtn) nextBtn.disabled = previewCurrentPage >= previewTotalPages;
    }

    function changePreviewPage(step) {
        const newPage = previewCurrentPage + step;
        if (newPage < 1 || newPage > previewTotalPages) return;
        previewCurrentPage = newPage;
        renderPreviewPage();
    }

    window.addEventListener('resize', () => { if (previewPdfDoc) renderPreviewPage(); });

    function selectDocument(id) {
        const doc = readyDocuments.find(d => d.id === id);
        if (!doc) return;

        document.getElementById('previewTitle').textContent = doc.title;
        document.getElementById('previewNumber').textContent = doc.document_number;

        loadPreviewPdf(doc.file_url);

        document.querySelectorAll('.doc-panel').forEach(panel => {
            const panelId = parseInt(panel.id.replace('panel-', ''));
            panel.classList.toggle('hidden', panelId !== id);
        });

        document.querySelectorAll('.doc-list-item').forEach(item => {
            const itemId = parseInt(item.id.replace('listitem-', ''));
            item.classList.toggle('bg-indigo-600/20', itemId === id);
            item.classList.toggle('border-indigo-500', itemId === id);
            item.classList.toggle('bg-slate-950/50', itemId !== id);
            item.classList.toggle('border-slate-800', itemId !== id);
        });

        closeAllPanels();
    }

    function togglePanel(which) {
        const list = document.getElementById('docListSidebar');
        const backdrop = document.getElementById('mobileBackdrop');
        const isOpen = !list.classList.contains('hidden');

        list.classList.add('hidden');
        backdrop.classList.add('hidden');

        if (!isOpen) {
            list.classList.remove('hidden');
            backdrop.classList.remove('hidden');
        }
    }

    function closeAllPanels() {
        document.getElementById('docListSidebar').classList.add('hidden');
        document.getElementById('mobileBackdrop').classList.add('hidden');
    }

    if (readyDocuments.length > 0) {
        loadPreviewPdf(readyDocuments[0].file_url);
    }
</script>

</body>
</html>