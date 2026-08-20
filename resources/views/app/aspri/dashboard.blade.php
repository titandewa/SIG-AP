<x-app-shell title="SIGAP - Dashboard ADMINISTRATOR" role="aspri" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.aspri.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('aspri.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
]">
@include('partials.fcm-notif')

    <div class="max-w-5xl mx-auto p-4 sm:p-6">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg mb-4">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-300 text-rose-800 text-sm px-4 py-3 rounded-lg mb-4">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Header List -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Daftar Pengajuan SK Aktif</h2>
                <p class="text-xs text-slate-500">Klik dokumen untuk melihat detail, status, dan diskusi.</p>
            </div>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-plus"></i> Buat Pengajuan Baru
            </button>
        </div>

        <!-- List SK Aktif (Simple Card) -->
        <div class="space-y-3">
            @forelse($documents as $document)
                @php
                    $unreadCount = $document->comments->where('is_read', false)->count();
                @endphp
                @php
                    $needsTtd = $document->status === 'DISETUJUI_TANPA_TTD';
                    $targetRoute = $needsTtd
                        ? route('aspri.ttd.editor', $document->id)
                        : route('aspri.detail', $document->id);
                @endphp
                <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md hover:border-indigo-300 transition relative cursor-pointer"
                    onclick="window.location.href='{{ $targetRoute }}'">

                    @if($unreadCount > 0)
                        <span class="absolute -top-2 -right-2 bg-rose-600 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center shadow">
                            {{ $unreadCount }}
                        </span>
                    @endif

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] text-slate-400">
                                {{ $document->created_at->translatedFormat('d F Y, H:i') }} • No: {{ $document->document_number }}
                            </p>
                            <h3 class="font-bold text-slate-800 text-sm mt-0.5 break-words">{{ $document->title }}</h3>
                        </div>
                        <div class="self-start sm:self-center shrink-0">
                            <span class="font-bold text-[10px] px-2.5 py-1 rounded-full uppercase tracking-wider inline-flex items-center
                                {{ $document->status === 'DITINJAU' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $document->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $document->status === 'SIAP_TTD' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $needsTtd ? 'bg-purple-100 text-purple-800' : '' }}">
                                @if(in_array($document->status, ['DITINJAU', 'PENDING']))
                                    <i class="fa-solid fa-spinner animate-spin mr-1"></i>
                                @elseif($needsTtd)
                                    <i class="fa-solid fa-pen-nib mr-1"></i>
                                @endif
                                @switch($document->status)
                                    @case('DITINJAU') Ditinjau @break
                                    @case('PENDING') Perlu Revisi @break
                                    @case('SIAP_TTD') Siap TTD @break
                                    @case('DISETUJUI_TANPA_TTD') Perlu Tempel TTD @break
                                    @default {{ $document->status }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <i class="fa-solid fa-file-circle-check text-3xl text-slate-300 mb-2 block"></i>
                    <p class="text-slate-400 text-sm">Belum ada pengajuan SK aktif.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Upload SK -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-circle-plus text-indigo-600"></i> Buat Pengajuan SK Baru
                    </h2>
                    <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form id="skForm" action="{{ route('aspri.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Judul / Perihal SK</label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: SK Pengangkatan Tim Dev SIGAP 2026" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        @error('title')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor SK</label>
                        <input type="text" name="document_number" value="{{ old('document_number') }}" placeholder="Contoh: 001/SK-DIR/SIGAP/2026" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        @error('document_number')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Upload Berkas SK (.pdf)</label>

                        <div id="dropZone" class="border-2 border-dashed border-slate-300 p-4 rounded-xl text-center cursor-pointer bg-slate-50 block transition">
                            <div id="dropZoneContent">
                                <i class="fa-solid fa-cloud-arrow-up text-2xl text-slate-400 mb-1"></i>
                                <p class="text-xs text-slate-500 font-medium" id="fileLabel">Klik atau seret file SK ke sini</p>
                                <span class="text-[10px] text-slate-400 block mt-1">Format: PDF (Max. 10MB)</span>
                            </div>
                            <div id="fileSelectedContent" class="hidden">
                                <i class="fa-solid fa-file-pdf text-2xl text-rose-500 mb-1"></i>
                                <p class="text-xs text-slate-700 font-semibold" id="fileNameText"></p>
                                <p class="text-[10px] text-slate-400" id="fileSizeText"></p>
                                <button type="button" id="removeFileBtn" class="text-[10px] text-rose-600 hover:underline mt-1">Hapus & pilih ulang</button>
                            </div>
                        </div>

                        <input type="file" name="pdf_file" id="fileInput" accept=".pdf" class="hidden" required>

                        @error('pdf_file')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-2">Pilih Validator</label>

                        <!-- Kolom Search Validator -->
                        <input type="text" id="petinggiSearch" onkeyup="filterPetinggi()" placeholder="Cari nama validator..."
                            class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg mb-2 focus:ring-2 focus:ring-indigo-500 outline-none">

                        <div id="petinggiListBox" class="space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs max-h-40 overflow-y-auto">
                            @forelse($petinggiList as $petinggi)
                                <div class="petinggi-item flex items-center justify-between gap-2 py-1 border-b border-slate-100 last:border-0">
                                    <label class="flex items-center gap-2 cursor-pointer flex-1">
                                        <input type="checkbox" class="petinggi-select rounded text-indigo-600" data-id="{{ $petinggi->id }}">
                                        <span>{{ $petinggi->name }}</span>
                                    </label>
                                    <div class="flex items-center gap-3 text-[10px] text-slate-500">
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="radio" name="mode_{{ $petinggi->id }}" value="validasi" class="mode-radio" data-id="{{ $petinggi->id }}" checked>
                                            Validasi only
                                        </label>
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="radio" name="mode_{{ $petinggi->id }}" value="sign" class="mode-radio" data-id="{{ $petinggi->id }}">
                                            Sign
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <p class="text-slate-400 italic">Belum ada user dengan role Validator.</p>
                            @endforelse
                        </div>

                        <!-- hidden inputs akan diisi via JS sebelum submit -->
                        <div id="hiddenPetinggiInputs"></div>
                        <p id="petinggiNoResult" class="hidden text-[11px] text-slate-400 italic mt-1">Tidak ada nama yang cocok.</p>

                        @error('petinggi_ids')
                            <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" id="submitBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
                        <span id="submitBtnText"><i class="fa-solid fa-paper-plane mr-1"></i> Ajukan & Kirim WA Notif</span>
                        <span id="submitBtnLoading" class="hidden items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Mengunggah...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        if (document.body.dataset.hasErrors === 'true') {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const dropZoneContent = document.getElementById('dropZoneContent');
        const fileSelectedContent = document.getElementById('fileSelectedContent');
        const fileNameText = document.getElementById('fileNameText');
        const fileSizeText = document.getElementById('fileSizeText');
        const removeFileBtn = document.getElementById('removeFileBtn');

        function showSelectedFile(file) {
            dropZoneContent.classList.add('hidden');
            fileSelectedContent.classList.remove('hidden');
            fileNameText.textContent = file.name;
            fileSizeText.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            dropZone.classList.remove('border-slate-300', 'hover:border-indigo-500');
            dropZone.classList.add('border-emerald-400', 'bg-emerald-50/30');
        }

        function resetDropZone() {
            fileInput.value = '';
            dropZoneContent.classList.remove('hidden');
            fileSelectedContent.classList.add('hidden');
            dropZone.classList.add('border-slate-300', 'hover:border-indigo-500');
            dropZone.classList.remove('border-emerald-400', 'bg-emerald-50/30');
        }

        dropZone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                showSelectedFile(fileInput.files[0]);
            }
        });

        removeFileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            resetDropZone();
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-indigo-500', 'bg-indigo-50/40');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-indigo-500', 'bg-indigo-50/40');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type === 'application/pdf') {
                fileInput.files = files;
                showSelectedFile(files[0]);
            } else if (files.length > 0) {
                alert('Hanya file PDF yang diperbolehkan.');
            }
        });

        const skForm = document.getElementById('skForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitBtnText = document.getElementById('submitBtnText');
        const submitBtnLoading = document.getElementById('submitBtnLoading');

        skForm.addEventListener('submit', () => {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtnText.classList.add('hidden');
            submitBtnLoading.classList.remove('hidden');
            submitBtnLoading.classList.add('flex');
        });

        // Fungsi Filter Validator secara Real-time
        function filterPetinggi() {
            const keyword = document.getElementById('petinggiSearch').value.toLowerCase();
            const items = document.querySelectorAll('.petinggi-item');
            let anyVisible = false;

            items.forEach(item => {
                const name = item.querySelector('span').textContent.toLowerCase();
                const match = name.includes(keyword);
                item.style.display = match ? 'flex' : 'none';
                if (match) anyVisible = true;
            });

            document.getElementById('petinggiNoResult').classList.toggle('hidden', anyVisible);
        }

        document.getElementById('skForm').addEventListener('submit', function (e) {
        const container = document.getElementById('hiddenPetinggiInputs');
        container.innerHTML = '';

        document.querySelectorAll('.petinggi-select:checked').forEach(function (checkbox) {
                const id = checkbox.dataset.id;
                const mode = document.querySelector(`input[name="mode_${id}"]:checked`).value;

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'petinggi_ids[]';
                idInput.value = id;
                container.appendChild(idInput);

                const signInput = document.createElement('input');
                signInput.type = 'hidden';
                signInput.name = `need_sign[${id}]`;
                signInput.value = mode === 'sign' ? 1 : 0;
                container.appendChild(signInput);
            });
        });
    </script>

</x-app-shell>