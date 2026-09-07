<x-app-shell title="SIGAP - Tempatkan Tanda Tangan" role="petinggi" roleColor="slate" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.petinggi.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('petinggi.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Atur TTD', 'url' => route('petinggi.signature.page'), 'icon' => 'fa-solid fa-signature'],
]">
    <style>
        @media (min-width: 1024px) {
            html, body { height: 100%; overflow: hidden; }
        }
        #pdfCanvas {
            display: block;
            width: 100% !important;
            height: auto !important;
        }
        .thumb-skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .signature-instance { touch-action: none; }
        .handle {
            position: absolute;
            width: 16px;
            height: 16px;
            background: #10b981;
            border: 2px solid #fff;
            border-radius: 50%;
            z-index: 5;
            touch-action: none;
        }
        @media (max-width: 1024px) {
            .handle { width: 22px; height: 22px; }
        }
        .handle-nw { top: -8px; left: -8px; cursor: nwse-resize; }
        .handle-n  { top: -8px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
        .handle-ne { top: -8px; right: -8px; cursor: nesw-resize; }
        .handle-e  { top: 50%; right: -8px; transform: translateY(-50%); cursor: ew-resize; }
        .handle-se { bottom: -8px; right: -8px; cursor: nwse-resize; }
        .handle-s  { bottom: -8px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
        .handle-sw { bottom: -8px; left: -8px; cursor: nesw-resize; }
        .handle-w  { top: 50%; left: -8px; transform: translateY(-50%); cursor: ew-resize; }
    </style>

    <div class="flex flex-col lg:h-[calc(100vh-64px)] -m-3 sm:-m-5 lg:-m-8 lg:overflow-hidden">
        <!-- Bar khusus toggle thumbnail & panel TTD (mobile only) -->
        <div class="lg:hidden bg-[#021b41] border-b border-[#021b41] px-3 py-2 flex items-center justify-between shrink-0">
            <span class="text-slate-300 text-xs font-semibold truncate">{{ $document->title }}</span>
        </div>

        <!-- Backdrop buat drawer mobile -->
        <div id="mobileBackdrop" onclick="closeAllPanels()" class="hidden fixed inset-0 bg-slate-900/50 z-20 lg:hidden"></div>

        <!-- Body: 3 kolom (desktop) / drawer (mobile) -->
        <div class="flex flex-1 min-h-0 relative">

            <!-- Sidebar kiri: thumbnail -->
            <aside id="thumbSidebar"
                class="hidden lg:block fixed lg:static inset-y-0 left-0 z-30 w-56 lg:w-40 bg-white border-r border-slate-200 overflow-y-auto p-2 shrink-0 shadow-xl lg:shadow-none"></aside>

            <!-- Tengah: viewer -->
            <main class="flex-1 flex flex-col min-w-0">
                <!-- Nav halaman -->
                <div class="flex items-center gap-1.5 px-2 sm:px-4 py-2 bg-white border-b border-slate-200 shrink-0 overflow-x-auto">
                    <button onclick="togglePanel('thumb')" class="lg:hidden w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                        <i class="fa-solid fa-images text-slate-600"></i>
                    </button>

                    <button id="prevPageBtn" onclick="changePage(-1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                        <i class="fa-solid fa-chevron-up"></i>
                    </button>
                    <div class="flex items-center gap-1 shrink-0">
                        <input type="number" id="pageInput" value="1" min="1" class="w-12 sm:w-16 text-center border border-slate-300 rounded-lg px-2 py-1 text-sm" onchange="goToPage(this.value)">
                        <span class="text-sm text-slate-500">/ <span id="totalPages">-</span></span>
                    </div>
                    <button id="nextPageBtn" onclick="changePage(1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>

                    <div class="flex items-center gap-1 border-l border-slate-200 pl-1.5 sm:pl-3 ml-0.5 sm:ml-1 shrink-0">
                        <button id="zoomOutBtn" onclick="zoomOut()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                            <i class="fa-solid fa-magnifying-glass-minus"></i>
                        </button>
                        <span id="zoomLevelText" class="text-xs text-slate-500 min-w-[38px] text-center">100%</span>
                        <button id="zoomInBtn" onclick="zoomIn()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition shrink-0">
                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                        </button>
                    </div>

                    <span class="text-xs text-slate-400 border-l border-slate-200 pl-3 ml-1 truncate max-w-xs hidden lg:inline">{{ $document->title }}</span>

                    <button onclick="togglePanel('sign')" class="lg:hidden w-8 h-8 rounded-lg bg-[#4DA8FF]/10 hover:bg-[#4DA8FF]/20 flex items-center justify-center transition shrink-0 ml-auto">
                        <i class="fa-solid fa-signature text-[#4DA8FF]"></i>
                    </button>
                </div>

                <!-- Area preview full, fit lebar -->
                <div id="viewerScroll" class="flex-1 overflow-auto bg-slate-100 p-4 sm:p-8 flex justify-center items-start">
                    <div id="pdfContainer" class="relative bg-white shadow-lg rounded-md h-fit my-2">
                        <canvas id="pdfCanvas"></canvas>
                    </div>
                </div>

                <div id="loadingText" class="text-center text-sm text-slate-500 py-2 bg-slate-200">
                    <i class="fa-solid fa-spinner animate-spin mr-1"></i> Memuat dokumen...
                </div>

                <div class="flex justify-end gap-2 p-3 sm:p-4 bg-white border-t border-slate-200 shrink-0">
                    <a href="{{ route('app.petinggi.dashboard') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-lg transition">
                        Batalkan
                    </a>
                    <button id="submitWithoutSignBtn" onclick="prosesSetujuiTanpaTtd()" class="hidden px-4 sm:px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-lg transition shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-clock"></i> <span class="hidden sm:inline">Setujui Tanpa TTD</span><span class="inline sm:hidden">Tanpa TTD</span>
                    </button>
                    <button id="submitBtn" onclick="prosesSimpanTtd()" class="px-4 sm:px-6 py-2.5 bg-[#4DA8FF] hover:brightness-110 text-white text-sm font-bold rounded-lg transition shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-stamp"></i> <span class="hidden sm:inline">Setujui & Simpan TTD</span><span class="inline sm:hidden">Setujui</span>
                    </button>
                </div>
            </main>

            <!-- Sidebar kanan: pilihan tanda tangan -->
            <aside id="signPanel"
                class="hidden lg:block fixed lg:static inset-x-0 bottom-0 lg:inset-auto z-30 lg:w-72 bg-white border-l border-slate-200 p-5 overflow-y-auto shrink-0 rounded-t-2xl lg:rounded-none shadow-xl lg:shadow-none max-h-[70vh] lg:max-h-none">
                <div class="flex justify-between items-center lg:block mb-1">
                    <h2 class="font-bold text-slate-900">Pilihan tanda tangan</h2>
                    <button onclick="closeAllPanels()" class="lg:hidden text-slate-400 hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4">Seret kotak di bawah ke posisi yang diinginkan pada dokumen. Boleh lebih dari satu, di halaman berbeda sekalipun.</p>

                <p class="text-xs font-semibold text-slate-600 mb-2">Kotak isian wajib diisi</p>
                <div id="dragSignatureSource" onclick="addSignatureAtCenter()"
                    class="flex items-center gap-2 border border-slate-200 rounded-xl p-2 cursor-grab active:cursor-grabbing hover:border-[#4DA8FF]/50 hover:bg-[#4DA8FF]/5 transition select-none touch-none">
                    <span class="w-8 h-8 rounded-lg bg-[#4DA8FF]/10 text-[#4DA8FF] flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-pen-nib"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-[#4DA8FF] leading-tight">Tanda tangan</p>
                        <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" class="h-6 object-contain" draggable="false">
                    </div>
                </div>

                <p class="text-xs text-slate-400 mt-4">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Klik kanan (atau tahan sebentar di HP) pada tanda tangan di dokumen untuk menghapus, atau tekan tombol silang merah. Tarik bulatan di tepi kotak untuk mengubah ukuran.
                </p>
            </aside>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.27/dist/interact.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        const pdfUrl = "{{ asset('storage/' . $document->latestVersion->file_path) }}";
        const signatureImageSrc = "{{ asset('storage/' . auth()->user()->signature_path) }}";

        let originalPdfBytes = null;
        let pdfDocInstance = null;
        let currentPage = 1;
        let totalPagesCount = 0;

        let signatureInstances = [];
        let instanceCounter = 0;
        const thumbRenderedPages = new Set();
        let thumbObserver = null;
        let zoomLevel = 1;
        let currentRenderTask = null;
        let currentRawViewport = null;
        const validatorPlacements = {!! json_encode($document->signaturePlacements->map(function ($p) {
            return [
                'page' => $p->page_number,
                'x' => $p->x,
                'y' => $p->y,
                'width' => $p->width,
                'height' => $p->height,
                'signature_path' => asset('storage/' . $p->signature_path),
                'name' => $p->user->name ?? 'Validator',
            ];
        })) !!};

        async function loadPdf() {
            if (!window.pdfjsLib) {
                document.getElementById('loadingText').innerHTML = '<i class="fa-solid fa-circle-exclamation mr-1"></i> Library PDF tidak bisa dimuat.';
                return;
            }

            const response = await fetch(pdfUrl, { cache: 'no-store' });
            if (!response.ok) {
                throw new Error('Gagal mengambil file PDF');
            }

            originalPdfBytes = await response.arrayBuffer();
            if (!originalPdfBytes.byteLength) {
                throw new Error('File PDF kosong');
            }

            const loadingTask = pdfjsLib.getDocument({ data: originalPdfBytes.slice(0) });
            pdfDocInstance = await loadingTask.promise;
            totalPagesCount = pdfDocInstance.numPages;

            document.getElementById('totalPages').textContent = totalPagesCount;
            document.getElementById('pageInput').max = totalPagesCount;

            currentPage = 1;
            document.getElementById('pageInput').value = currentPage;

            await buildThumbnailList();
            await renderPage(currentPage);
            document.getElementById('loadingText').classList.add('hidden');

            const viewerScroll = document.getElementById('viewerScroll');
            let resizeDebounce;
            const ro = new ResizeObserver(() => {
                clearTimeout(resizeDebounce);
                resizeDebounce = setTimeout(() => renderPage(currentPage), 150);
            });
            ro.observe(viewerScroll);

            window.addEventListener('resize', debounce(() => renderPage(currentPage), 250));
        }

        function debounce(fn, ms) {
            let t;
            return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
        }

        async function buildThumbnailList() {
            const sidebar = document.getElementById('thumbSidebar');
            sidebar.innerHTML = '';

            const firstPage = await pdfDocInstance.getPage(1);
            const ratio = firstPage.getViewport({ scale: 1 }).height / firstPage.getViewport({ scale: 1 }).width;
            const thumbWidth = 128;

            thumbObserver = new IntersectionObserver(onThumbIntersect, {
                root: sidebar, rootMargin: '200px 0px', threshold: 0.01
            });

            for (let i = 1; i <= totalPagesCount; i++) {
                const wrapper = document.createElement('div');
                wrapper.className = 'mb-3 cursor-pointer group';
                wrapper.dataset.page = i;

                const box = document.createElement('div');
                box.className = 'thumb-skeleton rounded-md border-2 border-transparent group-hover:border-[#4DA8FF] overflow-hidden';
                box.style.width = thumbWidth + 'px';
                box.style.height = Math.round(thumbWidth * ratio) + 'px';
                box.id = 'thumb-box-' + i;

                const label = document.createElement('p');
                label.className = 'text-center text-[11px] text-slate-500 mt-1';
                label.textContent = i;

                wrapper.appendChild(box);
                wrapper.appendChild(label);
                wrapper.onclick = () => goToPage(i);
                sidebar.appendChild(wrapper);

                thumbObserver.observe(box);
            }

            highlightActiveThumb();
        }

        async function onThumbIntersect(entries) {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                const box = entry.target;
                const pageNum = parseInt(box.id.replace('thumb-box-', ''));
                if (thumbRenderedPages.has(pageNum)) continue;
                thumbRenderedPages.add(pageNum);
                thumbObserver.unobserve(box);
                renderThumbPage(pageNum, box);
            }
        }

        async function renderThumbPage(pageNum, box) {
            const page = await pdfDocInstance.getPage(pageNum);
            const targetWidth = box.clientWidth || 128;
            const rawViewport = page.getViewport({ scale: 1 });
            const scale = targetWidth / rawViewport.width;
            const viewport = page.getViewport({ scale });
            const pixelRatio = window.devicePixelRatio || 1;

            const canvas = document.createElement('canvas');
            canvas.width = viewport.width * pixelRatio;
            canvas.height = viewport.height * pixelRatio;
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            const ctx = canvas.getContext('2d');
            ctx.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
            await page.render({ canvasContext: ctx, viewport }).promise;

            box.classList.remove('thumb-skeleton');
            box.innerHTML = '';
            box.appendChild(canvas);
        }

        function highlightActiveThumb() {
            document.querySelectorAll('#thumbSidebar > div').forEach(el => {
                const isActive = parseInt(el.dataset.page) === currentPage;
                const box = el.querySelector('div');
                box.classList.toggle('border-[#4DA8FF]', isActive);
                box.classList.toggle('border-transparent', !isActive);
            });
        }

        async function renderPage(num) {
            if (currentRenderTask) {
                try { currentRenderTask.cancel(); } catch (e) {}
                currentRenderTask = null;
            }

            const page = await pdfDocInstance.getPage(num);
            const rawViewport = page.getViewport({ scale: 1 });
            currentRawViewport = rawViewport;

            const viewerScroll = document.getElementById('viewerScroll');
            const availableWidth = viewerScroll.clientWidth - 40;
            const scaleByWidth = availableWidth / rawViewport.width;
            const currentRenderScale = Math.max(scaleByWidth, 0.1) * zoomLevel;

            const viewport = page.getViewport({ scale: currentRenderScale });
            const pixelRatio = window.devicePixelRatio || 1;

            const canvas = document.getElementById('pdfCanvas');
            const ctx = canvas.getContext('2d');

            canvas.width = viewport.width * pixelRatio;
            canvas.height = viewport.height * pixelRatio;
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            ctx.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

            const task = page.render({ canvasContext: ctx, viewport });
            currentRenderTask = task;

            try {
                await task.promise;
            } catch (err) {
                if (err && err.name === 'RenderingCancelledException') {
                    return;
                }
                throw err;
            } finally {
                if (currentRenderTask === task) {
                    currentRenderTask = null;
                }
            }

            updateNavButtons();
            renderVisibleSignatures();
            renderValidatorPlacements();
            highlightActiveThumb();
        }

        function updateNavButtons() {
            document.getElementById('prevPageBtn').disabled = currentPage <= 1;
            document.getElementById('nextPageBtn').disabled = currentPage >= totalPagesCount;
            document.getElementById('pageInput').value = currentPage;
        }

        async function changePage(step) {
            const newPage = currentPage + step;
            if (newPage < 1 || newPage > totalPagesCount) return;
            currentPage = newPage;
            await renderPage(currentPage);
        }

        async function goToPage(value) {
            let num = parseInt(value);
            if (isNaN(num) || num < 1) num = 1;
            if (num > totalPagesCount) num = totalPagesCount;
            currentPage = num;
            await renderPage(currentPage);
        }

        function updateZoomText() {
            const el = document.getElementById('zoomLevelText');
            if (el) el.textContent = Math.round(zoomLevel * 100) + '%';
        }

        async function zoomIn() {
            zoomLevel = Math.min(zoomLevel + 0.2, 3);
            updateZoomText();
            await renderPage(currentPage);
        }

        async function zoomOut() {
            zoomLevel = Math.max(zoomLevel - 0.2, 0.4);
            updateZoomText();
            await renderPage(currentPage);
        }

        function initSignatureSourceDrag() {
            const source = document.getElementById('dragSignatureSource');

            interact(source).draggable({
                inertia: false,
                autoScroll: true,
                listeners: {
                    start(event) {
                        const rect = event.target.getBoundingClientRect();
                        const clone = event.target.cloneNode(true);
                        clone.id = 'dragSignatureClone';
                        clone.style.position = 'fixed';
                        clone.style.left = rect.left + 'px';
                        clone.style.top = rect.top + 'px';
                        clone.style.width = rect.width + 'px';
                        clone.style.zIndex = 9999;
                        clone.style.pointerEvents = 'none';
                        clone.style.opacity = '0.85';
                        clone.setAttribute('data-x', rect.left);
                        clone.setAttribute('data-y', rect.top);
                        document.body.appendChild(clone);
                        event.target._cloneEl = clone;
                    },
                    move(event) {
                        const clone = event.target._cloneEl;
                        if (!clone) return;
                        const x = (parseFloat(clone.getAttribute('data-x')) || 0) + event.dx;
                        const y = (parseFloat(clone.getAttribute('data-y')) || 0) + event.dy;
                        clone.style.left = x + 'px';
                        clone.style.top = y + 'px';
                        clone.setAttribute('data-x', x);
                        clone.setAttribute('data-y', y);
                    },
                    end(event) {
                        const clone = event.target._cloneEl;
                        if (!clone) return;

                        const cloneRect = clone.getBoundingClientRect();
                        const dropCenterX = cloneRect.left + cloneRect.width / 2;
                        const dropCenterY = cloneRect.top + cloneRect.height / 2;

                        const container = document.getElementById('pdfContainer');
                        const containerRect = container.getBoundingClientRect();

                        const isOverDocument = (
                            dropCenterX >= containerRect.left && dropCenterX <= containerRect.right &&
                            dropCenterY >= containerRect.top && dropCenterY <= containerRect.bottom
                        );

                        if (isOverDocument) {
                            const relX = dropCenterX - containerRect.left - 80;
                            const relY = dropCenterY - containerRect.top - 35;
                            addSignatureInstance(relX, relY);
                            closeAllPanels();
                        }

                        clone.remove();
                        event.target._cloneEl = null;
                    }
                }
            });
        }

        function addSignatureInstance(x = null, y = null) {
            instanceCounter++;
            const id = 'sig-instance-' + instanceCounter;

            const container = document.getElementById('pdfContainer');
            const canvas = document.getElementById('pdfCanvas');

            const wrapper = document.createElement('div');
            wrapper.id = id;
            wrapper.className = 'signature-instance absolute cursor-move border-2 border-dashed border-indigo-500 bg-white/40';

            const defaultWidth = Math.min(Math.max(canvas.clientWidth * 0.35, 90), 300);
            const defaultHeight = defaultWidth * (210 / 480);

            wrapper.style.width = defaultWidth + 'px';
            wrapper.style.height = defaultHeight + 'px';
            wrapper.style.top = '0';
            wrapper.style.left = '0';

            const startX = x !== null ? Math.max(x, 0) : Math.max(canvas.clientWidth - defaultWidth - 20, 0);
            const startY = y !== null ? Math.max(y, 0) : Math.max(canvas.clientHeight - defaultHeight - 20 - (instanceCounter * 20), 0);

            wrapper.style.transform = `translate(${startX}px, ${startY}px)`;
            wrapper.setAttribute('data-x', startX);
            wrapper.setAttribute('data-y', startY);

            const img = document.createElement('img');
            img.src = signatureImageSrc;
            img.className = 'w-full h-full object-contain pointer-events-none';
            img.draggable = false;
            wrapper.appendChild(img);

            const delBtn = document.createElement('button');
            delBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            delBtn.className = 'absolute -top-3 -right-3 w-6 h-6 rounded-full bg-rose-500 hover:bg-rose-600 text-white text-xs flex items-center justify-center shadow z-10';
            delBtn.onclick = (e) => {
                e.stopPropagation();
                signatureInstances = signatureInstances.filter(i => i.element !== wrapper);
                wrapper.remove();
            };
            wrapper.appendChild(delBtn);

            ['nw','n','ne','e','se','s','sw','w'].forEach(dir => {
                const dot = document.createElement('span');
                dot.className = 'handle handle-' + dir;
                wrapper.appendChild(dot);
            });

            container.appendChild(wrapper);

            const instance = { id, page: currentPage, element: wrapper };
            signatureInstances.push(instance);

            initDraggable(wrapper);
            renderVisibleSignatures();
        }

        function renderVisibleSignatures() {
            signatureInstances.forEach(inst => {
                inst.element.style.display = (inst.page === currentPage) ? 'block' : 'none';
            });
        }

        function renderValidatorPlacements() {
            document.querySelectorAll('.validator-signature-overlay').forEach(el => el.remove());

            if (!currentRawViewport) return;

            const canvas = document.getElementById('pdfCanvas');
            const container = document.getElementById('pdfContainer');
            const scaleX = canvas.clientWidth / currentRawViewport.width;
            const scaleY = canvas.clientHeight / currentRawViewport.height;

            validatorPlacements
                .filter(vp => vp.page === currentPage)
                .forEach(vp => {
                    const visualWidth = vp.width * scaleX;
                    const visualHeight = vp.height * scaleY;
                    const visualX = vp.x * scaleX;
                    const visualY = (currentRawViewport.height - vp.y - vp.height) * scaleY;

                    const wrapper = document.createElement('div');
                    wrapper.className = 'validator-signature-overlay absolute border-2 border-dashed border-emerald-500 bg-white/40 pointer-events-none';
                    wrapper.style.width = visualWidth + 'px';
                    wrapper.style.height = visualHeight + 'px';
                    wrapper.style.left = visualX + 'px';
                    wrapper.style.top = visualY + 'px';

                    const img = document.createElement('img');
                    img.src = vp.signature_path;
                    img.className = 'w-full h-full object-contain';
                    wrapper.appendChild(img);

                    const label = document.createElement('span');
                    label.className = 'absolute -top-5 left-0 text-[9px] font-bold text-emerald-600 bg-white px-1 rounded';
                    label.textContent = 'TTD ' + vp.name;
                    wrapper.appendChild(label);

                    container.appendChild(wrapper);
                });
        }

        function addSignatureAtCenter() {
            const viewerScroll = document.getElementById('viewerScroll');
            const canvas = document.getElementById('pdfCanvas');
            if (!canvas || !canvas.clientWidth) return;

            const boxWidth = 160;
            const boxHeight = 70;

            const currentScrollX = viewerScroll.scrollLeft;
            const currentScrollY = viewerScroll.scrollTop;
            const visibleWidth = viewerScroll.clientWidth;
            const visibleHeight = viewerScroll.clientHeight;

            let centerX = currentScrollX + (visibleWidth - boxWidth) / 2;
            let centerY = currentScrollY + (visibleHeight - boxHeight) / 2;

            const maxLeft = Math.max(0, canvas.clientWidth - boxWidth);
            const maxTop = Math.max(0, canvas.clientHeight - boxHeight);

            centerX = Math.max(0, Math.min(centerX - (viewerScroll.offsetLeft || 0), maxLeft));
            centerY = Math.max(0, Math.min(centerY - (viewerScroll.offsetTop || 0), maxTop));

            const fallbackX = (canvas.clientWidth - boxWidth) / 2;
            const fallbackY = currentScrollY + (visibleHeight / 2) - boxHeight;

            const finalX = Math.max(0, Math.min(fallbackX, maxLeft));
            const finalY = Math.max(0, Math.min(fallbackY, maxTop));

            addSignatureInstance(finalX, finalY);
            closeAllPanels();
        }

        function initDraggable(el) {
            interact(el).draggable({
                ignoreFrom: '.handle, button',
                inertia: true,
                modifiers: [interact.modifiers.restrictRect({ restriction: 'parent', endOnly: true })],
                listeners: {
                    move(event) {
                        const target = event.target;
                        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                        target.style.transform = `translate(${x}px, ${y}px)`;
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                    }
                }
            }).resizable({
                edges: {
                    left: '.handle-w, .handle-nw, .handle-sw',
                    right: '.handle-e, .handle-ne, .handle-se',
                    top: '.handle-n, .handle-nw, .handle-ne',
                    bottom: '.handle-s, .handle-sw, .handle-se'
                },
                modifiers: [
                    interact.modifiers.restrictSize({ min: { width: 60, height: 30 } }),
                    interact.modifiers.restrictRect({ restriction: 'parent' })
                ],
                listeners: {
                    move(event) {
                        const target = event.target;
                        let x = (parseFloat(target.getAttribute('data-x')) || 0);
                        let y = (parseFloat(target.getAttribute('data-y')) || 0);
                        target.style.width = event.rect.width + 'px';
                        target.style.height = event.rect.height + 'px';
                        x += event.deltaRect.left;
                        y += event.deltaRect.top;
                        target.style.transform = `translate(${x}px, ${y}px)`;
                        target.setAttribute('data-x', x);
                        target.setAttribute('data-y', y);
                    }
                }
            });

            el.addEventListener('contextmenu', async function (e) {
                e.preventDefault();
                const confirmed = await openConfirmModal('Hapus tanda tangan ini?');
                if (confirmed) {
                    signatureInstances = signatureInstances.filter(i => i.element !== el);
                    el.remove();
                }
            });
        }

        // Inti bagian ini beda dari punya dirut: bukan bikin file PDF baru,
        // cuma kirim koordinat (page, x, y, width, height) tiap tanda tangan
        // dalam satuan asli halaman PDF (bukan pixel canvas), biar posisinya
        // konsisten dipakai ulang nanti pas file final dibuat.
        async function prosesSimpanTtd() {
            if (signatureInstances.length === 0) {
                showAlertModal('Tambahkan minimal satu tanda tangan terlebih dahulu.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-1"></i> Menyimpan...';

            try {
                const canvas = document.getElementById('pdfCanvas');
                const placements = [];

                for (const inst of signatureInstances) {
                    const pageForScale = await pdfDocInstance.getPage(inst.page);
                    const rawVp = pageForScale.getViewport({ scale: 1 });

                    const scaleX = rawVp.width / canvas.clientWidth;
                    const scaleY = rawVp.height / canvas.clientHeight;

                    const visualX = parseFloat(inst.element.getAttribute('data-x')) || 0;
                    const visualY = parseFloat(inst.element.getAttribute('data-y')) || 0;
                    const visualWidth = inst.element.clientWidth;
                    const visualHeight = inst.element.clientHeight;

                    const pdfWidth = visualWidth * scaleX;
                    const pdfHeight = visualHeight * scaleY;
                    const pdfX = visualX * scaleX;
                    const pdfY = rawVp.height - (visualY * scaleY) - pdfHeight;

                    placements.push({
                        page_number: inst.page,
                        x: pdfX,
                        y: pdfY,
                        width: pdfWidth,
                        height: pdfHeight,
                    });
                }

                const response = await fetch("{{ route('petinggi.ttd.save', $document->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ placements }),
                });

                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.href = "{{ route('app.petinggi.dashboard') }}";
                } else {
                    showAlertModal('Terjadi kesalahan saat menyimpan tanda tangan.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-stamp"></i> Setujui & Simpan TTD';
                }
            } catch (error) {
                console.error(error);
                showAlertModal('Terjadi kesalahan: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-stamp"></i> Setujui & Simpan TTD';
            }
        }

        async function prosesSetujuiTanpaTtd() {
            const confirmed = await openConfirmModal('Dokumen akan disetujui TANPA tanda tangan Anda. Admin akan menempelkan TTD Anda nanti. Lanjutkan?');
            if (!confirmed) {
                return;
            }

            const btn = document.getElementById('submitWithoutSignBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-1"></i> Memproses...';

            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch("{{ route('petinggi.sign-without-signature', $document->id) }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    await showAlertModal(data.message);
                    window.location.href = "{{ route('app.petinggi.dashboard') }}";
                } else {
                    showAlertModal(data.message || 'Terjadi kesalahan saat menyimpan.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-clock"></i> Setujui Tanpa TTD';
                }
            } catch (error) {
                console.error(error);
                showAlertModal('Terjadi kesalahan pada sistem.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-clock"></i> Setujui Tanpa TTD';
            }
        }

        function togglePanel(which) {
            const thumb = document.getElementById('thumbSidebar');
            const sign = document.getElementById('signPanel');
            const backdrop = document.getElementById('mobileBackdrop');

            const target = which === 'thumb' ? thumb : sign;
            const isOpen = !target.classList.contains('hidden');

            thumb.classList.add('hidden');
            sign.classList.add('hidden');
            backdrop.classList.add('hidden');

            if (!isOpen) {
                target.classList.remove('hidden');
                backdrop.classList.remove('hidden');
            }
        }

        function closeAllPanels() {
            document.getElementById('thumbSidebar').classList.add('hidden');
            document.getElementById('signPanel').classList.add('hidden');
            document.getElementById('mobileBackdrop').classList.add('hidden');
        }

        loadPdf();
        initSignatureSourceDrag();
    </script>

    <!-- Modal Confirm (pengganti native confirm, promise-based buat dipakai di fungsi async) -->
    <div id="modalConfirm" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <p id="confirmModalMessage" class="text-sm text-slate-700 mb-5"></p>
            <div class="flex gap-3">
                <button type="button" onclick="resolveConfirmModal(false)" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-xl text-sm transition">
                    Batal
                </button>
                <button type="button" onclick="resolveConfirmModal(true)" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <x-alert-modal />

    <script>
        let _confirmModalResolve = null;

        function openConfirmModal(message) {
            document.getElementById('confirmModalMessage').textContent = message;
            document.getElementById('modalConfirm').classList.remove('hidden');
            return new Promise((resolve) => {
                _confirmModalResolve = resolve;
            });
        }

        function resolveConfirmModal(result) {
            document.getElementById('modalConfirm').classList.add('hidden');
            if (_confirmModalResolve) {
                _confirmModalResolve(result);
                _confirmModalResolve = null;
            }
        }
    </script>
</x-app-shell>