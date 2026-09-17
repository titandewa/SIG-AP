<x-app-shell title="SIGAP - Review SK (Validator)" role="petinggi" roleColor="slate" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.petinggi.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('petinggi.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Atur TTD', 'url' => route('petinggi.signature.page'), 'icon' => 'fa-solid fa-signature'],
]">
    <style>
        @media (min-width: 1024px) {
            html, body { height: 100%; overflow: hidden; }
        }
        #docListSidebar::-webkit-scrollbar, #reviewPanel::-webkit-scrollbar { width: 8px; }
        #docListSidebar::-webkit-scrollbar-thumb, #reviewPanel::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
    @include('partials.fcm-notif')

    @php
        // Dokumen yang otomatis di-preview saat halaman pertama kali dibuka:
        // cuma yang statusnya MENUNGGU (belum kamu putuskan). Kalau semua
        // dokumen sudah direspon (atau memang belum ada dokumen sama sekali),
        // area preview cukup tampilkan keterangan teks, tidak buka PDF apapun.
        $initialPendingApp = $approvals->firstWhere('status', 'MENUNGGU');
        $hasInitialPreview = $initialPendingApp && $initialPendingApp->document->latestVersion;
    @endphp

<div class="flex flex-col lg:h-[calc(100vh-64px)] -m-3 sm:-m-5 lg:-m-8 lg:overflow-hidden">
    <!-- Bar khusus toggle daftar dokumen (mobile only) -->
    <div class="lg:hidden bg-[#021b41] border-b border-[#021b41] px-4 py-2.5 flex items-center justify-between shrink-0">
        <span class="text-slate-300 text-xs font-semibold">Lembar Review SK</span>
        <button onclick="togglePanel('list')" class="bg-[#021b41] hover:bg-[#03265c] px-3 py-1.5 rounded-lg text-xs text-slate-300 flex items-center gap-1.5 transition">
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

    <!-- Backdrop mobile (hanya untuk drawer list dokumen) -->
    <div id="mobileBackdrop" onclick="closeAllPanels()" class="hidden fixed inset-0 bg-[#021b41]/50 z-20 lg:hidden"></div>

    <div class="flex flex-col lg:flex-row flex-1 min-h-0 relative">

        <!-- Sidebar Kiri: List Dokumen (tetap drawer di HP) -->
        <aside id="docListSidebar"
            class="hidden lg:flex flex flex-col fixed lg:static inset-y-0 left-0 z-30 w-72 bg-[#021b41] border-r border-[#021b41] shrink-0 shadow-xl lg:shadow-none lg:h-full lg:overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b border-[#021b41] lg:hidden">
                <span class="text-white font-bold text-sm">Daftar Dokumen</span>
                <button onclick="closeAllPanels()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @forelse($approvals as $app)
                    @php $isDecided = $app->status !== 'MENUNGGU'; @endphp
                    <button onclick="selectDocument({{ $app->document_id }})" id="listitem-{{ $app->document_id }}"
                        class="doc-list-item w-full text-left p-3 rounded-xl border transition
                        {{ $isDecided ? 'opacity-60' : '' }}
                        {{ ($initialPendingApp && $initialPendingApp->id === $app->id) ? 'bg-[#4DA8FF]/10 border-[#4DA8FF]' : 'bg-[#021b41]/55 border-[#021b41] hover:border-[#4DA8FF]/60 hover:bg-[#4DA8FF]/5' }}">
                        <div class="flex justify-between items-start gap-2">
                            <p class="font-semibold text-white text-xs leading-snug line-clamp-2">{{ $app->document->title }}</p>
                            <x-approval-status-badge :status="$app->status" />
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
                        {{ $initialPendingApp->document->title ?? 'Tidak ada dokumen menunggu keputusan' }}
                    </h2>
                    <p class="text-xs text-slate-500 truncate">
                        @if($initialPendingApp)
                            No: <span id="previewNumber">{{ $initialPendingApp->document->document_number }}</span> •
                            <span id="previewPengaju">{{ $initialPendingApp->document->user->name ?? '-' }}</span>
                        @else
                            <span id="previewNumber"></span><span id="previewPengaju"></span>
                        @endif
                    </p>
                </div>
                <div id="previewPagerControls" class="flex items-center gap-2 shrink-0 flex-wrap {{ $hasInitialPreview ? '' : 'hidden' }}">
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
            </div>

            <!-- Tinggi preview dibatasi di HP (55vh) supaya tombol & chat di bawahnya tetap kelihatan tanpa scroll jauh -->
            <div id="previewViewport" class="h-[55vh] lg:flex-1 lg:h-auto lg:min-h-0 bg-slate-200 flex justify-center items-center p-3 sm:p-6 overflow-hidden">
                <canvas id="previewCanvas" class="shadow-md bg-white {{ $hasInitialPreview ? '' : 'hidden' }}"></canvas>
                <div id="previewPlaceholder" class="text-slate-400 text-sm text-center {{ $hasInitialPreview ? 'hidden' : '' }}">
                    <i class="fa-solid fa-clipboard-check text-3xl mb-2 block"></i>
                    <p>
                        @if($approvals->isEmpty())
                            Belum ada dokumen baru untuk ditinjau.
                        @else
                            Semua dokumen sudah kamu berikan tanggapan.
                        @endif
                    </p>
                </div>
            </div>
        </main>

        <!-- Kanan: Review & Chat -- SELALU TAMPIL, di bawah preview pada HP, jadi sidebar di desktop -->
        <aside id="reviewPanel"
            class="flex flex-col bg-[#FFFFFF] border-t lg:border-t-0 lg:border-l border-slate-200 shrink-0 lg:w-96 lg:overflow-y-auto p-4">

            @if($approvals->isEmpty())
                <div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-200">
                    <i class="fa-solid fa-file-circle-check text-3xl text-slate-300 mb-2 block"></i>
                    <p class="text-slate-400 text-xs">Belum ada dokumen yang perlu ditinjau saat ini.</p>
                </div>
            @else
                @foreach($approvals as $app)
                    @php
                        // Dicek per dokumen: apakah petinggi ini (user login) sudah pernah
                        // kirim komentar khusus untuk dokumen ini. $app->document->comments
                        // sudah otomatis ter-scope ke document_id ini saja.
                        $hasCommented = $app->document->comments->where('user_id', auth()->id())->count() > 0;
                        $isInitial = $initialPendingApp && $initialPendingApp->id === $app->id;
                    @endphp
                    <div id="panel-{{ $app->document_id }}" class="doc-panel space-y-3 {{ $isInitial ? '' : 'hidden' }}">

                        <!-- Status semua Validator yang ditugaskan untuk dokumen ini -->
                        <div class="bg-slate-50 rounded-lg p-3 space-y-2 border border-slate-200">
                            <p class="text-[10px] font-bold text-[#4DA8FF] uppercase tracking-wide">Status Validator</p>
                            @foreach($app->document->approvals as $reviewer)
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-bold text-slate-800 truncate">
                                        {{ Str::of($reviewer->user->name ?? 'Validator')->before(' ') }}
                                        @if($reviewer->user_id === auth()->id())
                                            <span class="text-slate-400 font-normal text-xs">(Kamu)</span>
                                        @endif
                                    </span>
                                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-bold uppercase text-[#4DA8FF] bg-[#4DA8FF]/10">
                                        {{ $reviewer->status === 'MENUNGGU' ? 'Menunggu' : $reviewer->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Box komentar SELALU ada di DOM (walau kosong) supaya JS bisa
                             nambahin bubble baru tanpa reload halaman -->
                        <div id="commentsBox-{{ $app->document_id }}"
                            class="bg-slate-50 rounded-lg p-3 space-y-2 max-h-48 overflow-y-auto border border-slate-200 {{ $app->document->comments->count() > 0 ? '' : 'hidden' }}">
                            @foreach($app->document->comments as $comment)
                                @php $isMine = $comment->user_id === auth()->id(); @endphp
                                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[80%] {{ $isMine ? 'bg-[#4DA8FF] text-white' : 'bg-white border border-slate-200 text-slate-700' }} rounded-lg px-3 py-2">
                                        <p class="text-[11px] leading-snug">{{ $comment->comment }}</p>
                                        <p class="text-[9px] mt-1 {{ $isMine ? 'text-white/80' : 'text-slate-400' }}">
                                            {{ $isMine ? 'Kamu' : ($comment->user->name ?? 'User') }} • {{ $comment->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form id="commentForm-{{ $app->document_id }}"
                            action="{{ route('document.comment', $app->document_id) }}" method="POST"
                            onsubmit="return submitComment(event, {{ $app->document_id }})"
                            class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="comment" id="commentInput-{{ $app->document_id }}" placeholder="Tulis pesan..." required autocomplete="off"
                                class="flex-1 bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#4DA8FF] transition">
                            <button type="submit" class="shrink-0 w-9 h-9 rounded-lg bg-[#4DA8FF] hover:brightness-110 text-white flex items-center justify-center transition">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </form>

                        <form action="{{ route('petinggi.review', $app->document_id) }}" method="POST" class="space-y-3 pt-2 border-t border-slate-200" data-doc-id="{{ $app->document_id }}">
                            @csrf
                            @if($app->status === 'MENUNGGU')
                                <div class="grid grid-cols-2 lg:grid-cols-1 gap-2 pt-1">
                                    <button type="button" onclick="askConfirm(this.closest('form'), 'TERIMA', 'MENYETUJUI dokumen ini')"
                                        class="px-2 sm:px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] sm:text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-check hidden sm:inline"></i> <span>Setujui</span>
                                    </button>
                                    <button type="button" id="revisiBtn-{{ $app->document_id }}" {{ !$hasCommented ? 'disabled' : '' }}
                                        onclick="askConfirm(this.closest('form'), 'PENDING', 'meminta REVISI (Pastikan poin revisi telah dicantumkan pada kolom komentar sebelum melanjutkan)')"
                                        class="px-2 sm:px-3 py-2 text-white text-[11px] sm:text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1
                                        {{ $hasCommented ? 'bg-amber-600 hover:bg-amber-500' : 'bg-slate-300 opacity-50 cursor-not-allowed' }}">
                                        <i class="fa-solid fa-clock-rotate-left hidden sm:inline"></i> <span>Revisi</span>
                                    </button>
                                    {{--
                                    <button type="button" {{ !$hasCommented ? 'disabled' : '' }}
                                        onclick="askConfirm(this.closest('form'), 'TOLAK', 'MENOLAK dokumen ini')"
                                        class="px-2 sm:px-3 py-2 text-white text-[11px] sm:text-xs font-semibold rounded-lg transition flex items-center justify-center gap-1
                                        {{ $hasCommented ? 'bg-rose-600 hover:bg-rose-500' : 'bg-slate-300 opacity-50 cursor-not-allowed' }}">
                                        <i class="fa-solid fa-xmark hidden sm:inline"></i> <span>Tolak</span>
                                    </button>
                                    --}}
                                </div>
                                @if(!$hasCommented)
                                    <p id="revisiLockMsg-{{ $app->document_id }}" class="text-[10px] text-amber-500 text-center mt-1">
                                        <i class="fa-solid fa-lock mr-1"></i>Kirim pesan dulu untuk membuka tombol Revisi/Tolak.
                                    </p>
                                @endif
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

        $siblingsAllTerima = $app->document->approvals
            ->where('role', 'validator')
            ->where('id', '!=', $app->id)
            ->every(fn($a) => $a->status === 'TERIMA');

        $existingPlacementsForJs = $app->document->signaturePlacements->map(function ($p) {
            return [
                'page' => $p->page_number,
                'x' => $p->x,
                'y' => $p->y,
                'width' => $p->width,
                'height' => $p->height,
                'signature_path' => asset('storage/' . $p->signature_path),
                'name' => $p->user->name ?? 'Penandatangan',
            ];
        })->values();

        $approvalsForJs[] = [
            'id' => $app->document_id,
            'title' => $app->document->title,
            'document_number' => $app->document->document_number,
            'pengaju' => $app->document->user->name ?? '-',
            'file_url' => $app->document->latestVersion ? asset('storage/' . $app->document->latestVersion->file_path) : null,
            'versions' => $versionsForJs,
            'status' => $app->status,
            'needSign' => (bool) $app->need_sign,
            'siblingsAllTerima' => $siblingsAllTerima,
            'existingPlacements' => $existingPlacementsForJs,
        ];
    }
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>
<script>
    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    const reviewDocuments = {!! json_encode($approvalsForJs) !!};
    let previewPdfDoc = null;
    let previewCurrentPage = 1;
    let previewTotalPages = 0;

    function showPreviewPlaceholder() {
        const canvas = document.getElementById('previewCanvas');
        const placeholder = document.getElementById('previewPlaceholder');
        const pagerControls = document.getElementById('previewPagerControls');
        if (canvas) canvas.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
        if (pagerControls) pagerControls.classList.add('hidden');
    }

    async function loadPreviewPdf(url) {
        if (!url) {
            showPreviewPlaceholder();
            return;
        }
        if (!window.pdfjsLib) {
            const viewport = document.getElementById('previewViewport');
            if (viewport) {
                viewport.innerHTML = '<div class="text-center text-sm text-rose-600"><i class="fa-solid fa-file-circle-xmark text-3xl mb-2 block"></i><p>Library PDF tidak bisa dimuat.</p></div>';
            }
            return;
        }
        const viewport = document.getElementById('previewViewport');
        const canvas = document.getElementById('previewCanvas');
        const placeholder = document.getElementById('previewPlaceholder');
        const pagerControls = document.getElementById('previewPagerControls');
        const pageInfo = document.getElementById('previewPageInfo');

        if (viewport) viewport.classList.add('bg-slate-200');
        if (placeholder) placeholder.classList.add('hidden');
        if (canvas) canvas.classList.remove('hidden');
        if (canvas) canvas.style.display = 'block';
        if (pagerControls) pagerControls.classList.remove('hidden');
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

    let currentRenderTask = null;

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

        if (currentRenderTask) {
            currentRenderTask.cancel();
        }
        currentRenderTask = page.render({ canvasContext: ctx, viewport });

        try {
            await currentRenderTask.promise;
        } catch (e) {
            if (e && e.name === 'RenderingCancelledException') return;
            throw e;
        }

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
            item.classList.toggle('bg-[#4DA8FF]/10', itemId === id);
            item.classList.toggle('border-[#4DA8FF]', itemId === id);
            item.classList.toggle('bg-[#021b41]/55', itemId !== id);
            item.classList.toggle('border-[#021b41]', itemId !== id);
        });

        closeAllPanels();
    }

    // Drawer cuma dipakai untuk list dokumen sekarang
    function togglePanel(which) {
        const list = document.getElementById('docListSidebar');
        const backdrop = document.getElementById('mobileBackdrop');
        const isOpen = !list.classList.contains('hidden');

        list.classList.add('hidden');
        list.classList.remove('flex');
        backdrop.classList.add('hidden');

        if (!isOpen) {
            list.classList.remove('hidden');
            list.classList.add('flex');
            backdrop.classList.remove('hidden');
        }
    }

    function closeAllPanels() {
        const list = document.getElementById('docListSidebar');
        list.classList.add('hidden');
        list.classList.remove('flex');
        document.getElementById('mobileBackdrop').classList.add('hidden');
    }

    // ===== Kirim komentar via AJAX, tanpa reload halaman =====
    async function submitComment(event, docId) {
        event.preventDefault();

        const form = document.getElementById(`commentForm-${docId}`);
        const input = document.getElementById(`commentInput-${docId}`);
        const text = input.value.trim();
        if (!text) return false;

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('comment', text);

            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!res.ok) throw new Error('Gagal mengirim pesan');
            const data = await res.json();

            appendCommentBubble(docId, data.comment);
            input.value = '';
            unlockRevisiButton(docId);
        } catch (err) {
            console.error(err);
            showAlertModal('Gagal mengirim pesan, coba lagi.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }

        return false;
    }

    function appendCommentBubble(docId, comment) {
        const box = document.getElementById(`commentsBox-${docId}`);
        if (!box) return;
        box.classList.remove('hidden');

        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-end';

        const bubble = document.createElement('div');
        bubble.className = 'max-w-[80%] bg-[#4DA8FF] text-white rounded-lg px-3 py-2';

        const textEl = document.createElement('p');
        textEl.className = 'text-[11px] leading-snug';
        textEl.textContent = comment.comment;

        const metaEl = document.createElement('p');
        metaEl.className = 'text-[9px] mt-1 text-white/80';
        metaEl.textContent = 'Kamu • ' + (comment.created_at || 'baru saja');

        bubble.appendChild(textEl);
        bubble.appendChild(metaEl);
        wrapper.appendChild(bubble);
        box.appendChild(wrapper);
        box.scrollTop = box.scrollHeight;
    }

    function unlockRevisiButton(docId) {
        const btn = document.getElementById(`revisiBtn-${docId}`);
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('bg-slate-300', 'opacity-50', 'cursor-not-allowed');
            btn.classList.add('bg-amber-600', 'hover:bg-amber-500');
        }
        const lockMsg = document.getElementById(`revisiLockMsg-${docId}`);
        if (lockMsg) lockMsg.classList.add('hidden');
    }

    // Auto-preview: kalau ada dokumen yang di-fokus lewat notifikasi (?doc=),
    // buka itu apa pun statusnya. Kalau tidak, hanya auto-buka dokumen
    // pertama yang statusnya MENUNGGU. Kalau semua sudah direspon (atau
    // memang belum ada dokumen), biarkan placeholder teks yang tampil,
    // tidak ada PDF yang otomatis dibuka.
    const focusDocId = @json($focusDocId ?? null);
    const focusDoc = focusDocId ? reviewDocuments.find(d => d.id === parseInt(focusDocId)) : null;

    if (focusDoc) {
        selectDocument(focusDoc.id);
    } else {
        const firstPending = reviewDocuments.find(d => d.status === 'MENUNGGU');
        if (firstPending) {
            selectDocument(firstPending.id);
        }
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

    async function embedSignatureOnPdf(pdfDocRef, imgUrl, pageNumber, x, y, width, height) {
        const imgBytes = await fetch(imgUrl).then(r => r.arrayBuffer());
        let img;
        try {
            img = await pdfDocRef.embedPng(imgBytes);
        } catch {
            img = await pdfDocRef.embedJpg(imgBytes);
        }
        const page = pdfDocRef.getPage(pageNumber - 1);
        page.drawImage(img, { x, y, width, height });
    }

    async function confirmYes() {
        if (!pendingForm || !pendingStatus) return;

        const docId = parseInt(pendingForm.dataset.docId);
        const doc = reviewDocuments.find(d => d.id === docId);

        document.getElementById('confirmModal').classList.add('hidden');

        let hidden = pendingForm.querySelector('input[name="status"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'status';
            pendingForm.appendChild(hidden);
        }
        hidden.value = pendingStatus;

        const needsFlatten = pendingStatus === 'TERIMA'
            && doc && !doc.needSign
            && doc.siblingsAllTerima
            && doc.existingPlacements && doc.existingPlacements.length > 0;

        if (!needsFlatten) {
            pendingForm.submit();
            return;
        }

        // Approval ini bakal jadi penyelesai terakhir tapi gak punya TTD sendiri —
        // flatten dulu TTD yang sudah ada di DB biar gak ketinggalan di file final.
        try {
            const response = await fetch(doc.file_url, { cache: 'no-store' });
            const originalBytes = await response.arrayBuffer();

            const { PDFDocument } = PDFLib;
            const pdfDoc = await PDFDocument.load(originalBytes);

            for (const p of doc.existingPlacements) {
                await embedSignatureOnPdf(pdfDoc, p.signature_path, p.page, p.x, p.y, p.width, p.height);
            }

            const flattenedBytes = await pdfDoc.save();
            const blob = new Blob([flattenedBytes], { type: 'application/pdf' });

            const formData = new FormData(pendingForm);
            formData.append('signed_pdf', blob, 'signed.pdf');

            const res = await fetch(pendingForm.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData,
            });

            if (res.redirected) {
                window.location.href = res.url;
            } else if (res.ok) {
                window.location.reload();
            } else {
                showAlertModal('Terjadi kesalahan saat menyelesaikan dokumen.');
            }
        } catch (err) {
            console.error(err);
            showAlertModal('Terjadi kesalahan: ' + err.message);
        }
    }
</script>

</x-app-shell>