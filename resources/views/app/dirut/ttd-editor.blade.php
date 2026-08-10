<x-app-shell title="SIGAP - Tandatangani Dokumen" role="dirut" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.dirut.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('dirut.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Atur TTD', 'url' => route('dirut.signature.page'), 'icon' => 'fa-solid fa-signature'],
]">
    <style>
        @media (min-width: 1024px) {
            html, body { height: 100%; overflow: hidden; }
        }
        /* Style tambahan untuk memastikan canvas tajam dan responsif di berbagai device */
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
    </style>

    <div class="flex flex-col lg:h-[calc(100vh-64px)] -m-3 sm:-m-5 lg:-m-8 lg:overflow-hidden">
        <!-- Bar khusus toggle thumbnail & panel TTD (mobile only) -->
        <div class="lg:hidden bg-indigo-950 border-b border-indigo-900 px-3 py-2 flex items-center justify-between shrink-0">
            <span class="text-indigo-200 text-xs font-semibold truncate">{{ $document->title }}</span>
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

                    <button onclick="togglePanel('sign')" class="lg:hidden w-8 h-8 rounded-lg bg-indigo-50 hover:bg-indigo-100 flex items-center justify-center transition shrink-0 ml-auto">
                        <i class="fa-solid fa-signature text-indigo-600"></i>
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
                    <a href="{{ route('app.dirut.dashboard') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm font-semibold rounded-lg transition">
                        Batalkan
                    </a>
                    <button id="submitBtn" onclick="prosesTandaTangan()" class="px-4 sm:px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-stamp"></i> <span class="hidden sm:inline">Sahkan Dokumen</span><span class="inline sm:hidden">Sahkan</span>
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
                <p class="text-xs text-slate-500 mb-4">Seret kotak di bawah ke posisi yang diinginkan pada dokumen.</p>

                <p class="text-xs font-semibold text-slate-600 mb-2">Kotak isian wajib diisi</p>
                <div id="dragSignatureSource" onclick="addSignatureAtCenter()"
                    class="flex items-center gap-2 border border-slate-200 rounded-xl p-2 cursor-grab active:cursor-grabbing hover:border-indigo-300 hover:bg-indigo-50/50 transition select-none touch-none">
                    <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-pen-nib"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold text-indigo-600 leading-tight">Tanda tangan</p>
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
        let pdfPageWidth = 0;   
        let pdfPageHeight = 0;
        let currentRenderScale = 1; 

        let signatureInstances = [];
        let instanceCounter = 0;
        const thumbRenderedPages = new Set();
        let thumbObserver = null;
        let zoomLevel = 1; 

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

            currentPage = totalPagesCount; 
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
                box.className = 'thumb-skeleton rounded-md border-2 border-transparent group-hover:border-indigo-300 overflow-hidden';
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
                box.classList.toggle('border-indigo-500', isActive);
                box.classList.toggle('border-transparent', !isActive);
            });
        }

        async function renderPage(num) {
            const page = await pdfDocInstance.getPage(num);
            const rawViewport = page.getViewport({ scale: 1 });
            pdfPageWidth = rawViewport.width;
            pdfPageHeight = rawViewport.height;

            const viewerScroll = document.getElementById('viewerScroll');
            const availableWidth = viewerScroll.clientWidth - 40;   
            const scaleByWidth = availableWidth / rawViewport.width;
            currentRenderScale = Math.max(scaleByWidth, 0.1) * zoomLevel; 

            const viewport = page.getViewport({ scale: currentRenderScale });
            const pixelRatio = window.devicePixelRatio || 1;

            const canvas = document.getElementById('pdfCanvas');
            const ctx = canvas.getContext('2d');

            // Mengatasi masalah blur dengan resolusi device pixel ratio
            canvas.width = viewport.width * pixelRatio;
            canvas.height = viewport.height * pixelRatio;
            canvas.style.width = viewport.width + 'px';
            canvas.style.height = viewport.height + 'px';
            ctx.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

            await page.render({ canvasContext: ctx, viewport }).promise;

            updateNavButtons();
            renderVisibleSignatures();
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
            wrapper.style.width = '480px';
            wrapper.style.height = '210px';
            wrapper.style.top = '0';
            wrapper.style.left = '0';

            const startX = x !== null ? Math.max(x, 0) : Math.max(canvas.clientWidth - 200, 0);
            const startY = y !== null ? Math.max(y, 0) : Math.max(canvas.clientHeight - 100 - (instanceCounter * 20), 0);

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

        function addSignatureAtCenter() {
            const viewerScroll = document.getElementById('viewerScroll');
            const canvas = document.getElementById('pdfCanvas');
            if (!canvas || !canvas.clientWidth) return;

            const boxWidth = 160;
            const boxHeight = 70;

            // Hitung posisi tengah berdasarkan area yang sedang aktif di-scroll (viewport container)
            const currentScrollX = viewerScroll.scrollLeft;
            const currentScrollY = viewerScroll.scrollTop;
            const visibleWidth = viewerScroll.clientWidth;
            const visibleHeight = viewerScroll.clientHeight;

            // Tentukan titik tengah relatif terhadap container scroll
            let centerX = currentScrollX + (visibleWidth / boxWidth) * 0 + (visibleWidth - boxWidth) / 2;
            let centerY = currentScrollY + (visibleHeight - boxHeight) / 2;

            // Pastikan posisi tidak keluar dari batas ukuran canvas PDF
            const maxLeft = Math.max(0, canvas.clientWidth - boxWidth);
            const maxTop = Math.max(0, canvas.clientHeight - boxHeight);

            // Konversi koordinat agar pas di tengah area canvas yang tampil
            centerX = Math.max(0, Math.min(centerX - (viewerScroll.offsetLeft || 0), maxLeft));
            centerY = Math.max(0, Math.min(centerY - (viewerScroll.offsetTop || 0), maxTop));

            // Fallback sederhana jika perhitungan container terhadap scroll pas di tengah-tengah dokumen
            const fallbackX = (canvas.clientWidth - boxWidth) / 2;
            const fallbackY = currentScrollY + (visibleHeight / 2) - boxHeight;

            finalX = Math.max(0, Math.min(fallbackX, maxLeft));
            finalY = Math.max(0, Math.min(fallbackY, maxTop));

            addSignatureInstance(finalX, finalY);
            closeAllPanels();
        }

        function initDraggable(el) {
            interact(el).draggable({
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
                edges: { left: true, right: true, bottom: true, top: true },
                margin: 10,
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

            el.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                if (confirm('Hapus tanda tangan ini?')) {
                    signatureInstances = signatureInstances.filter(i => i.element !== el);
                    el.remove();
                }
            });
        }

        function generateRandomHash() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = 'SIGAP-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-';
            for (let i = 0; i < 8; i++) result += chars.charAt(Math.floor(Math.random() * chars.length));
            return result;
        }

        async function prosesTandaTangan() {
            if (signatureInstances.length === 0) {
                alert('Tambahkan minimal satu tanda tangan terlebih dahulu.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-1"></i> Memproses...';

            try {
                const qrHash = generateRandomHash();
                const verifyUrl = "{{ url('/verify') }}/" + qrHash;
                const qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" + encodeURIComponent(verifyUrl);
                const qrImageBytes = await fetch(qrApiUrl).then(res => res.arrayBuffer());
                const signatureImageBytes = await fetch(signatureImageSrc).then(res => res.arrayBuffer());

                const pdfDoc = await PDFLib.PDFDocument.load(originalPdfBytes, { ignoreEncryption: true });
                const pngSignature = await pdfDoc.embedPng(signatureImageBytes);
                const pngQr = await pdfDoc.embedPng(qrImageBytes);
                const pages = pdfDoc.getPages();

                for (const inst of signatureInstances) {
                    const targetPage = pages[inst.page - 1];
                    const pageForScale = await pdfDocInstance.getPage(inst.page);
                    const rawVp = pageForScale.getViewport({ scale: 1 });

                    const scaleX = rawVp.width / document.getElementById('pdfCanvas').clientWidth;
                    const scaleY = rawVp.height / document.getElementById('pdfCanvas').clientHeight;

                    const visualX = parseFloat(inst.element.getAttribute('data-x')) || 0;
                    const visualY = parseFloat(inst.element.getAttribute('data-y')) || 0;
                    const visualWidth = inst.element.clientWidth;
                    const visualHeight = inst.element.clientHeight;

                    const pdfX = visualX * scaleX;
                    const pdfWidth = visualWidth * scaleX;
                    const pdfHeight = visualHeight * scaleY;
                    const pdfY = rawVp.height - (visualY * scaleY) - pdfHeight;

                    targetPage.drawImage(pngSignature, { x: pdfX, y: pdfY, width: pdfWidth, height: pdfHeight });

                    const qrSize = Math.min(pdfHeight, 40);
                    const qrX = pdfX + pdfWidth + 8;
                    const qrY = pdfY + (pdfHeight - qrSize) / 2;
                    targetPage.drawImage(pngQr, { x: qrX, y: qrY, width: qrSize, height: qrSize });
                }

                const modifiedPdfBytes = await pdfDoc.save();

                const formData = new FormData();
                const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });
                formData.append('signed_pdf', blob, 'signed.pdf');
                formData.append('qr_hash', qrHash);
                formData.append('_token', '{{ csrf_token() }}');

                const response = await fetch("{{ route('dirut.sign', $document->id) }}", { method: 'POST', body: formData });

                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.href = "{{ route('app.dirut.dashboard') }}";
                } else {
                    alert('Terjadi kesalahan saat menyimpan dokumen.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-stamp"></i> Sahkan Dokumen';
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-stamp"></i> Sahkan Dokumen';
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

</x-app-shell>