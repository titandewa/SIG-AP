<x-app-shell title="SIGAP - Review SK (Validator)" role="petinggi" roleColor="slate" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.petinggi.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('petinggi.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
]">
    <style>
        @media (min-width: 1024px) {
            html, body { height: 100%; overflow: hidden; }
        }
        #docListSidebar::-webkit-scrollbar, #reviewPanel::-webkit-scrollbar { width: 8px; }
        #docListSidebar::-webkit-scrollbar-thumb, #reviewPanel::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
    @include('partials.fcm-notif')

<div class="flex flex-col lg:h-[calc(100vh-64px)] -m-3 sm:-m-5 lg:-m-8 lg:overflow-hidden">
    <!-- Bar khusus toggle daftar dokumen (mobile only) -->
    <div class="lg:hidden bg-slate-900 border-b border-slate-800 px-4 py-2.5 flex items-center justify-between shrink-0">
        <span class="text-slate-300 text-xs font-semibold">Lembar Review SK</span>
        <button onclick="togglePanel('list')" class="bg-slate-800 hover:bg-slate-700 px-3 py-1.5 rounded-lg text-xs text-slate-300 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-list"></i> Daftar Dokumen
        </button>
    </div>

    @if(session('success'))
        <div class="px-4 pt-3 shrink-0">
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        </div>
    @endif

    @php
        // Semua approval tetap ditampilkan (bukan cuma yang MENUNGGU). Controller sudah
        // mengurutkan: MENUNGGU (belum diputuskan) di atas, sudah diputuskan di bawah.
    @endphp

    <!-- Backdrop mobile (hanya untuk drawer list dokumen) -->
    <div id="mobileBackdrop" onclick="closeAllPanels()" class="hidden fixed inset-0 bg-slate-900/50 z-20 lg:hidden"></div>

    <div class="flex flex-col lg:flex-row flex-1 min-h-0 relative">

        <!-- Sidebar Kiri: List Dokumen (tetap drawer di HP) -->
        <aside id="docListSidebar"
            class="hidden lg:flex lg:flex-col fixed lg:static inset-y-0 left-0 z-30 w-72 bg-slate-900 border-r border-slate-800 shrink-0 shadow-xl lg:shadow-none lg:h-full lg:overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b border-slate-800 lg:hidden">
                <span class="text-white font-bold text-sm">Daftar Dokumen</span>
                <button onclick="closeAllPanels()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @forelse($approvals as $app)
                    @php $isDecided = $app->status !== 'MENUNGGU'; @endphp
                    <button onclick="selectDocument({{ $app->document_id }})" id="listitem-{{ $app->document_id }}"
                        class="doc-list-item w-full text-left p-3 rounded-xl border transition
                        {{ $isDecided ? 'opacity-60' : '' }}
                        {{ $loop->first ? 'bg-indigo-600/20 border-indigo-500' : 'bg-slate-950/50 border-slate-800 hover:border-slate-700' }}">
                        <div class="flex justify-between items-start gap-2">
                            <p class="font-semibold text-white text-xs leading-snug line-clamp-2">{{ $app->document->title }}</p>
                            <span class="shrink-0 px-1.5 py-0.5 text-[9px] font-bold rounded uppercase
                                {{ $app->status === 'PENDING' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                {{ $app->status === 'TOLAK' ? 'bg-rose-500/20 text-rose-400' : '' }}
                                {{ $app->status === 'TERIMA' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                {{ $app->status === 'DIBATALKAN' ? 'bg-slate-600/40 text-slate-400' : '' }}
                                {{ $app->status === 'MENUNGGU' ? 'bg-slate-700 text-slate-300' : '' }}">
                                {{ $app->status }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">No: {{ $app->document->document_number }}</p>
                        <p class="text-[10px] text-slate-500">{{ $app->document->user->name ?? 'ADMINISTRATOR' }}</p>
                    </button>
                @empty
                    <div class="text-center py-10">
                        <i class="fa-solid fa-file-circle-check text-3xl text-slate-700 mb-2 block"></i>
                        <p class="text-slate-500 text-xs">Belum ada dokumen untuk ditinjau.</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- Tengah: Preview PDF -->
        <main class="flex flex-col min-w-0 bg-white lg:flex-1">
            <div class="p-3 sm:p-4 border-b border-slate-200 flex justify-between items-center gap-3 shrink-0 flex-wrap">
                <div class="min-w-0">
                    <h2 id="previewTitle" class="font-bold text-slate-900 text-sm truncate">
                        {{ $approvals->first()->document->title ?? 'Belum ada dokumen' }}
                    </h2>
                    <p class="text-xs text-slate-500 truncate">
                        No: <span id="previewNumber">{{ $approvals->first()->document->document_number ?? '-' }}</span> •
                        <span id="previewPengaju">{{ $approvals->first()->document->user->name ?? '-' }}</span>
                    </p>
                </div>
                @if($approvals->first() && $approvals->first()->document->latestVersion)
                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <select id="versionSelect" onchange="switchVersion(this.value)"
                            class="text-xs border border-slate-300 rounded-lg px-2 py-1.5 outline-none focus:border-indigo-500 max-w-[140px] sm:max-w-none">
                        </select>
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

            <!-- Tinggi preview dibatasi di HP (55vh) supaya tombol & chat di bawahnya tetap kelihatan tanpa scroll jauh -->
            <div id="previewViewport" class="h-[55vh] lg:flex-1 lg:h-auto lg:min-h-0 bg-slate-200 flex justify-center items-center p-3 sm:p-6 overflow-hidden">
                @if($approvals->first() && $approvals->first()->document->latestVersion)
                    <canvas id="previewCanvas" class="shadow-md bg-white"></canvas>
                @else
                    <div class="text-slate-400 text-sm text-center">
                        <i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i> Tidak ada berkas untuk ditampilkan.
                    </div>
                @endif
            </div>
        </main>

        <!-- Kanan: Review & Chat -- SELALU TAMPIL, di bawah preview pada HP, jadi sidebar di desktop -->
        <aside id="reviewPanel"
            class="flex flex-col bg-slate-900 border-t lg:border-t-0 lg:border-l border-slate-800 shrink-0 lg:w-96 lg:overflow-y-auto p-4">

            @if($approvals->isEmpty())
                <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-slate-800">
                    <i class="fa-solid fa-file-circle-check text-3xl text-slate-600 mb-2 block"></i>
                    <p class="text-slate-400 text-xs">Belum ada dokumen yang perlu ditinjau saat ini.</p>
                </div>
            @else
                @foreach($approvals as $app)
                    <div id="panel-{{ $app->document_id }}" class="doc-panel space-y-3 {{ $loop->first ? '' : 'hidden' }}">

                        <!-- Status semua Validator yang ditugaskan untuk dokumen ini -->
                        <div class="bg-slate-800/60 rounded-lg p-3 space-y-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Status Validator</p>
                            @foreach($app->document->approvals as $reviewer)
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs text-slate-200 truncate">
                                        {{ $reviewer->user->name ?? 'Validator' }}
                                        @if($reviewer->user_id === auth()->id())
                                            <span class="text-slate-500">(Kamu)</span>
                                        @endif
                                    </span>
                                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase
                                        {{ $reviewer->status === 'TERIMA' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                        {{ $reviewer->status === 'TOLAK' ? 'bg-rose-500/20 text-rose-400' : '' }}
                                        {{ $reviewer->status === 'PENDING' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                        {{ $reviewer->status === 'DIBATALKAN' ? 'bg-slate-600/40 text-slate-400' : '' }}
                                        {{ $reviewer->status === 'MENUNGGU' ? 'bg-slate-700 text-slate-300' : '' }}">
                                        {{ $reviewer->status === 'MENUNGGU' ? 'Belum Menjawab' : $reviewer->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if($app->document->comments->count() > 0)
                            <div class="bg-slate-800/60 rounded-lg p-3 space-y-2 max-h-48 overflow-y-auto">
                                @foreach($app->document->comments as $comment)
                                    @php $isMine = $comment->user_id === auth()->id(); @endphp
                                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-[80%] {{ $isMine ? 'bg-indigo-600 text-white' : 'bg-slate-700 text-slate-100' }} rounded-lg px-3 py-2">
                                            <p class="text-[11px] leading-snug">{{ $comment->comment }}</p>
                                            <p class="text-[9px] mt-1 {{ $isMine ? 'text-indigo-200' : 'text-slate-400' }}">
                                                {{ $isMine ? 'Kamu' : ($comment->user->name ?? 'User') }} • {{ $comment->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('document.comment', $app->document_id) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="comment" placeholder="Tulis pesan..." required autocomplete="off"
                                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                            <button type="submit" class="shrink-0 w-9 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center transition">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </form>

                        <form action="{{ route('petinggi.review', $app->document_id) }}" method="POST" class="space-y-3 pt-2 border-t border-slate-800">
                            @csrf
                            @if($app->status === 'MENUNGGU')
                                <div class="grid grid-cols-3 gap-2 pt-1">
                                    <button type="button" onclick="askConfirm(this.closest('form'), 'TERIMA', 'MENYETUJUI dokumen ini')"
                                        class="px-2 sm:px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] sm:text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-check hidden sm:inline"></i> <span>Setujui</span>
                                    </button>
                                    <button type="button" onclick="askConfirm(this.closest('form'), 'PENDING', 'meminta REVISI untuk dokumen ini')"
                                        class="px-2 sm:px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-[11px] sm:text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-clock-rotate-left hidden sm:inline"></i> <span>Revisi</span>
                                    </button>
                                    <button type="button" onclick="askConfirm(this.closest('form'), 'TOLAK', 'MENOLAK dokumen ini')"
                                        class="px-2 sm:px-3 py-2 bg-rose-600 hover:bg-rose-500 text-white text-[11px] sm:text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-xmark hidden sm:inline"></i> <span>Tolak</span>
                                    </button>
                                </div>
                            @else
                                <p class="text-[11px] text-slate-500 text-center py-1">
                                    <i class="fa-solid fa-circle-check mr-1"></i>
                                    Kamu sudah memberikan keputusan <b>{{ $app->status === 'MENUNGGU' ? '' : $app->status }}</b> untuk dokumen ini.
                                </p>
                            @endif
                        </form>
                    </div>
                @endforeach
            @endif
        </aside>
    </div>
</div>

@php
    $approvalsForJs = [];
    foreach ($approvals as $app) {
        $versionsForJs = [];
        foreach ($app->document->versions->sortByDesc('version_number') as $v) {
            $versionsForJs[] = [
                'version_number' => $v->version_number,
                'file_url' => asset('storage/' . $v->file_path),
            ];
        }
        $approvalsForJs[] = [
            'id' => $app->document_id,
            'title' => $app->document->title,
            'document_number' => $app->document->document_number,
            'pengaju' => $app->document->user->name ?? '-',
            'file_url' => $app->document->latestVersion ? asset('storage/' . $app->document->latestVersion->file_path) : null,
            'versions' => $versionsForJs,
        ];
    }
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    const reviewDocuments = {!! json_encode($approvalsForJs) !!};
    let previewPdfDoc = null;
    let previewCurrentPage = 1;
    let previewTotalPages = 0;

    async function loadPreviewPdf(url) {
        if (!url) return;
        if (!window.pdfjsLib) {
            const viewport = document.getElementById('previewViewport');
            if (viewport) {
                viewport.innerHTML = '<div class="text-center text-sm text-rose-600"><i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i><p>Library PDF tidak bisa dimuat.</p></div>';
            }
            return;
        }
        const viewport = document.getElementById('previewViewport');
        const canvas = document.getElementById('previewCanvas');
        const pageInfo = document.getElementById('previewPageInfo');

        if (viewport) viewport.classList.add('bg-slate-200');
        if (canvas) canvas.style.display = 'block';
        if (pageInfo) pageInfo.textContent = '- / -';

        try {
            const response = await fetch(url, { cache: 'no-store' });
            if (!response.ok) throw new Error('Gagal mengambil file PDF');
            const bytes = await response.arrayBuffer();
            if (!bytes.byteLength) throw new Error('File PDF kosong');

            previewPdfDoc = await pdfjsLib.getDocument({ data: bytes }).promise;
            previewTotalPages = previewPdfDoc.numPages;
            previewCurrentPage = 1;
            await renderPreviewPage();
        } catch (e) {
            console.error('Gagal memuat PDF preview:', e);
            if (viewport) {
                viewport.classList.remove('bg-slate-200');
                viewport.classList.add('bg-rose-50');
            }
            if (canvas) canvas.style.display = 'none';
            if (pageInfo) pageInfo.textContent = 'Tidak bisa dibuka';
            if (viewport) {
                viewport.innerHTML = '<div class="text-center text-sm text-rose-600"><i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i><p>File PDF tidak dapat dibuka saat ini.</p></div>';
            }
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

    function populateVersionDropdown(doc) {
        const select = document.getElementById('versionSelect');
        if (!select) return;
        select.innerHTML = doc.versions.map((v, i) =>
            `<option value="${v.file_url}">v${v.version_number}${i === 0 ? ' (Terbaru)' : ''}</option>`
        ).join('');
    }

    function switchVersion(url) {
        loadPreviewPdf(url);
    }

    function selectDocument(id) {
        const doc = reviewDocuments.find(d => d.id === id);
        if (!doc) return;

        document.getElementById('previewTitle').textContent = doc.title;
        document.getElementById('previewNumber').textContent = doc.document_number;
        document.getElementById('previewPengaju').textContent = doc.pengaju;

        populateVersionDropdown(doc);
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

    // Drawer cuma dipakai untuk list dokumen sekarang
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

    if (reviewDocuments.length > 0) {
        populateVersionDropdown(reviewDocuments[0]);
        loadPreviewPdf(reviewDocuments[0].file_url);
    }
</script>

<!-- Modal Konfirmasi Tindakan Review -->
<div id="confirmModal" class="hidden fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
        </div>
        <h3 class="font-bold text-slate-900 mb-1">Konfirmasi Tindakan</h3>
        <p id="confirmText" class="text-sm text-slate-500 mb-5"></p>
        <div class="flex gap-2">
            <button onclick="closeConfirm()" class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">Batal</button>
            <button onclick="confirmYes()" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script>
    let pendingForm = null;
    let pendingStatus = null;

    function askConfirm(form, statusValue, actionLabel) {
        pendingForm = form;
        pendingStatus = statusValue;
        let text = 'Yakin mau ' + actionLabel + '?';
        if (statusValue === 'TOLAK') text += ' Setelah ditolak, proses review untuk validator lain juga akan berhenti.';
        document.getElementById('confirmText').textContent = text;
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirm() {
        document.getElementById('confirmModal').classList.add('hidden');
        pendingForm = null;
        pendingStatus = null;
    }

    function confirmYes() {
        if (!pendingForm || !pendingStatus) return;
        let hidden = pendingForm.querySelector('input[name="status"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'status';
            pendingForm.appendChild(hidden);
        }
        hidden.value = pendingStatus;
        pendingForm.submit();
    }
</script>

</x-app-shell>