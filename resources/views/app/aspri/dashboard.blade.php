<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Dashboard Aspri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">

    <!-- Topbar -->
    <nav class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 p-2 rounded-lg font-bold text-xl">S</div>
            <div>
                <h1 class="font-bold text-lg leading-none">SIGAP_V1</h1>
                <span class="text-xs text-indigo-200">Sistem Pengajuan & Approval SK</span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-indigo-800 text-xs px-3 py-1 rounded-full border border-indigo-700">Role: Aspri / Admin</span>
            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center font-bold">A</div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-6">

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
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Daftar Pengajuan SK Aktif</h2>
                <p class="text-xs text-slate-500">Pantau proses persetujuan dan kirim reminder otomatis.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('aspri.riwayat') }}" class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                </a>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-plus"></i> Buat Pengajuan Baru
                </button>
            </div>
        </div>

        <!-- List SK Aktif -->
        <div class="space-y-4">
            @forelse($documents as $document)
                @php
                    $unreadCount = $document->comments->where('is_read', false)->count();
                @endphp
                <div class="bg-white border border-slate-200 rounded-xl p-4 hover:shadow-md transition relative cursor-pointer"
                    onclick="window.location.href='{{ route('aspri.detail', $document->id) }}'">

                    @if($unreadCount > 0)
                        <span class="absolute -top-2 -right-2 bg-rose-600 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center shadow">
                            {{ $unreadCount }}
                        </span>
                    @endif

                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider
                                {{ $document->status === 'DITINJAU' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $document->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $document->status === 'SIAP_TTD' ? 'bg-blue-100 text-blue-800' : '' }}">
                                @if(in_array($document->status, ['DITINJAU', 'PENDING']))
                                    <i class="fa-solid fa-spinner animate-spin mr-1"></i>
                                @endif
                                {{ $document->status }}
                            </span>
                            <h3 class="font-bold text-slate-800 text-base mt-1">{{ $document->title }}</h3>
                            <p class="text-xs text-slate-500">
                                No: {{ $document->document_number }} •
                                Diajukan: {{ $document->created_at->translatedFormat('d F Y') }}
                                @if($document->latestVersion)
                                    • File: {{ $document->latestVersion->original_filename }}
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(in_array($document->status, ['DITINJAU', 'PENDING']))
                                <div class="text-right countdown-box" data-created="{{ $document->created_at->toIso8601String() }}">
                                    <p class="text-[10px] text-slate-400 mb-1 countdown-label">Sisa Waktu Batas 24 Jam</p>
                                    <p class="countdown-text font-bold text-sm text-slate-700">-</p>
                                </div>
                            @endif

                            <form action="{{ route('aspri.reminder', $document->id) }}" method="POST" onclick="event.stopPropagation()">
                                @csrf
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition shadow-sm flex items-center gap-1.5">
                                    <i class="fa-brands fa-whatsapp text-sm"></i> Send Auto-WA Reminder
                                </button>
                            </form>
                        </div>
                    </div>

                    <hr class="my-3 border-slate-200">

                    <div class="text-xs">
                        <p class="font-semibold text-slate-700 mb-2">Status Persetujuan Petinggi:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($document->approvals as $approval)
                                <div class="flex items-center justify-between bg-white p-2.5 rounded-lg border
                                    {{ $approval->status === 'MENUNGGU' ? 'border-amber-300 bg-amber-50/30' : 'border-slate-200' }}">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full
                                            {{ $approval->status === 'TERIMA' ? 'bg-emerald-500' : '' }}
                                            {{ $approval->status === 'MENUNGGU' ? 'bg-amber-500 animate-pulse' : '' }}
                                            {{ $approval->status === 'PENDING' ? 'bg-amber-500' : '' }}
                                            {{ $approval->status === 'TOLAK' ? 'bg-rose-500' : '' }}">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $approval->user->name ?? 'Petinggi' }}</p>
                                            <p class="text-[10px] {{ $approval->status === 'MENUNGGU' ? 'text-amber-600 font-medium' : 'text-slate-400' }}">
                                                {{ $approval->status === 'MENUNGGU' ? 'Belum Menanggapi' : 'Terakhir update: '.$approval->updated_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="font-bold text-[10px] px-2 py-0.5 rounded
                                        {{ $approval->status === 'TERIMA' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                        {{ $approval->status === 'MENUNGGU' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $approval->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : '' }}
                                        {{ $approval->status === 'TOLAK' ? 'bg-rose-100 text-rose-800' : '' }}">
                                        {{ $approval->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($document->status === 'PENDING')
                        <div class="mt-3 pt-3 border-t border-amber-200 bg-amber-50/50 -mx-4 px-4 pb-1">
                            <p class="text-[11px] text-amber-700 font-semibold mb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation"></i> Petinggi meminta revisi. Unggah berkas terbaru:
                            </p>
                            <form action="{{ route('aspri.revisi', $document->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2" onclick="event.stopPropagation()">
                                @csrf
                                <input type="file" name="pdf_file" accept=".pdf" required
                                    class="text-[11px] flex-1 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-700 file:cursor-pointer">
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold px-3 py-2 rounded-lg transition shrink-0">
                                    <i class="fa-solid fa-upload mr-1"></i> Kirim Revisi
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($document->comments->count() > 0)
                        <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 italic">
                            <i class="fa-regular fa-comment-dots mr-1"></i>
                            {{ $document->comments->count() }} komentar dari Petinggi
                            @if($unreadCount > 0)
                                <span class="text-rose-600 font-semibold not-italic">({{ $unreadCount }} belum dibaca)</span>
                            @endif
                        </div>
                    @endif
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
                        <label class="block text-xs font-semibold text-slate-600 mb-2">Pilih Petinggi Peninjau (Reviewer)</label>
                        <div class="space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs">
                            @forelse($petinggiList as $petinggi)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="petinggi_ids[]" value="{{ $petinggi->id }}" class="rounded text-indigo-600">
                                    <span>{{ $petinggi->name }}</span>
                                </label>
                            @empty
                                <p class="text-slate-400 italic">Belum ada user dengan role petinggi.</p>
                            @endforelse
                        </div>
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
        function updateCountdowns() {
            document.querySelectorAll('.countdown-box').forEach(box => {
                const created = new Date(box.dataset.created);
                const deadline = new Date(created.getTime() + 24 * 60 * 60 * 1000);
                const now = new Date();
                const diff = deadline - now;
                const textEl = box.querySelector('.countdown-text');
                const labelEl = box.querySelector('.countdown-label');

                if (diff <= 0) {
                    const overdue = Math.abs(diff);
                    const hours = Math.floor(overdue / (1000 * 60 * 60));
                    const minutes = Math.floor((overdue % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((overdue % (1000 * 60)) / 1000);
                    textEl.textContent = `${hours}j ${minutes}m ${seconds}d`;
                    textEl.className = 'countdown-text font-bold text-sm text-rose-600';
                    if (labelEl) labelEl.textContent = 'Terlambat';
                    if (labelEl) labelEl.className = 'text-[10px] text-rose-500 mb-1 font-semibold countdown-label';
                } else {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    textEl.textContent = `${hours}j ${minutes}m ${seconds}d`;
                    textEl.className = 'countdown-text font-bold text-sm ' + (hours < 3 ? 'text-rose-600' : 'text-slate-700');
                    if (labelEl) labelEl.textContent = 'Sisa Waktu Batas 24 Jam';
                    if (labelEl) labelEl.className = 'text-[10px] text-slate-400 mb-1 countdown-label';
                }
            });
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);

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
    </script>

</body>
</html>