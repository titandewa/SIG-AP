<x-app-shell title="SIGAP - Atur Tanda Tangan" role="dirut" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.dirut.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('dirut.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Atur TTD', 'url' => route('dirut.signature.page'), 'icon' => 'fa-solid fa-signature'],
]">

    <div class="max-w-xl mx-auto">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg mb-4">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Tanda Tangan Digital Anda</h2>
            <p class="text-xs text-slate-500 mb-4">Tanda tangan ini akan otomatis dipakai setiap kali Anda mengesahkan dokumen SK. Cukup diatur sekali.</p>

            @if(auth()->user()->signature_path)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-600 mb-2">Tanda Tangan Tersimpan Saat Ini:</p>
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 flex justify-center">
                        <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" alt="Tanda Tangan" class="h-20">
                    </div>
                </div>
            @endif

            <!-- Tab switcher -->
            <div class="flex gap-1.5 mb-4 bg-slate-100 p-1 rounded-lg">
                <button type="button" id="tabDrawBtn" onclick="switchTab('draw')"
                    class="flex-1 text-xs font-semibold py-2 rounded-md transition bg-white text-indigo-600 shadow-sm">
                    <i class="fa-solid fa-pen mr-1"></i> Gambar
                </button>
                <button type="button" id="tabUploadBtn" onclick="switchTab('upload')"
                    class="flex-1 text-xs font-semibold py-2 rounded-md transition text-slate-500">
                    <i class="fa-solid fa-image mr-1"></i> Upload Foto
                </button>
            </div>

            <!-- Tab: Gambar manual -->
            <div id="tabDraw">
                <p class="text-xs font-semibold text-slate-600 mb-2">Gambar Tanda Tangan Baru:</p>
                <canvas id="signatureCanvas" class="border-2 border-dashed border-slate-300 rounded-xl w-full bg-white cursor-crosshair" height="200"></canvas>

                <div class="flex items-center gap-2 mt-3">
                    <button type="button" onclick="clearCanvas()" class="text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 px-3 py-2 rounded-lg hover:bg-rose-100 transition">
                        <i class="fa-solid fa-eraser mr-1"></i> Hapus
                    </button>
                    <span class="text-[11px] text-slate-400">Gambar pakai mouse atau jari (di HP/tablet)</span>
                </div>
            </div>

            <!-- Tab: Upload foto -->
            <div id="tabUpload" class="hidden">
                <p class="text-xs font-semibold text-slate-600 mb-2">Upload Foto Tanda Tangan:</p>
                <p class="text-[11px] text-slate-400 mb-3">Foto tanda tangan di atas kertas putih polos dengan tinta gelap, hasil terbaik saat pencahayaan rata.</p>

                <input type="file" id="ttdUpload" accept="image/*" class="block w-full text-sm text-slate-600 border border-slate-300 rounded-lg cursor-pointer file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-semibold mb-4">

                <div id="uploadEditorSection" class="hidden">
                    <div class="mb-4">
                        <label class="flex justify-between text-xs font-semibold text-slate-600 mb-1">
                            <span>Sensitivitas background</span>
                            <span id="thresholdValue">180</span>
                        </label>
                        <input type="range" id="thresholdSlider" min="80" max="240" value="180" class="w-full accent-indigo-600">
                        <p class="text-[11px] text-slate-400 mt-1">Geser ke kanan kalau background masih terlihat, geser ke kiri kalau tanda tangan ikut hilang.</p>
                    </div>

                    <div class="border border-slate-200 rounded-xl p-4 bg-[repeating-conic-gradient(#f1f5f9_0%_25%,white_0%_50%)] bg-[length:16px_16px] flex justify-center">
                        <canvas id="uploadPreviewCanvas" class="max-w-full"></canvas>
                    </div>
                </div>
            </div>

            <form id="signatureForm" action="{{ route('dirut.signature.save') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="signature_image" id="signatureImageInput">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-md">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Tanda Tangan
                </button>
            </form>
        </div>
    </div>

    <x-alert-modal />

    <script src="{{ asset('js/signature-editor.js') }}"></script>
    <script>
        // ================== Tab switching ==================
        let activeTab = 'draw';

        function switchTab(tab) {
            activeTab = tab;
            const tabDraw = document.getElementById('tabDraw');
            const tabUpload = document.getElementById('tabUpload');
            const tabDrawBtn = document.getElementById('tabDrawBtn');
            const tabUploadBtn = document.getElementById('tabUploadBtn');

            if (tab === 'draw') {
                tabDraw.classList.remove('hidden');
                tabUpload.classList.add('hidden');
                tabDrawBtn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
                tabDrawBtn.classList.remove('text-slate-500');
                tabUploadBtn.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
                tabUploadBtn.classList.add('text-slate-500');
            } else {
                tabDraw.classList.add('hidden');
                tabUpload.classList.remove('hidden');
                tabUploadBtn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
                tabUploadBtn.classList.remove('text-slate-500');
                tabDrawBtn.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
                tabDrawBtn.classList.add('text-slate-500');
            }
        }

        // ================== Tab: Gambar manual ==================
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        function resizeCanvas() {
            const ratio = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#1e293b';
        }
        resizeCanvas();

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches.length > 0) {
                return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
            }
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }

        function startDraw(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDraw() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDraw);

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // ================== Tab: Upload foto ==================
        SignatureEditor.init('uploadPreviewCanvas');

        const uploadInput = document.getElementById('ttdUpload');
        const uploadEditorSection = document.getElementById('uploadEditorSection');
        const thresholdSlider = document.getElementById('thresholdSlider');
        const thresholdValue = document.getElementById('thresholdValue');

        uploadInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            SignatureEditor.loadFile(file, () => {
                SignatureEditor.applyThreshold(parseInt(thresholdSlider.value));
                uploadEditorSection.classList.remove('hidden');
            });
        });

        let sliderDebounce;
        thresholdSlider.addEventListener('input', (e) => {
            thresholdValue.textContent = e.target.value;
            clearTimeout(sliderDebounce);
            sliderDebounce = setTimeout(() => SignatureEditor.applyThreshold(parseInt(e.target.value)), 10);
        });

        // ================== Submit gabungan ==================
        document.getElementById('signatureForm').addEventListener('submit', function(e) {
            if (activeTab === 'draw') {
                const blank = document.createElement('canvas');
                blank.width = canvas.width;
                blank.height = canvas.height;
                if (canvas.toDataURL() === blank.toDataURL()) {
                    e.preventDefault();
                    showAlertModal('Silakan gambar tanda tangan terlebih dahulu.');
                    return;
                }
                document.getElementById('signatureImageInput').value = canvas.toDataURL('image/png');
            } else {
                if (!SignatureEditor.hasImage()) {
                    e.preventDefault();
                    showAlertModal('Silakan upload foto tanda tangan terlebih dahulu.');
                    return;
                }
                document.getElementById('signatureImageInput').value = document.getElementById('uploadPreviewCanvas').toDataURL('image/png');
            }
        });
    </script>

</x-app-shell>