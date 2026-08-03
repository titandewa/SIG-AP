<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Review SK (Petinggi)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-slate-900 text-white px-4 sm:px-6 py-3 sm:py-4 flex flex-wrap justify-between items-center gap-y-2 shadow-md">
        <div class="flex items-center gap-3">
            <h1 class="font-bold text-base sm:text-lg leading-none">
                SIGAP_V1 <span class="text-xs text-indigo-400 font-normal">/ Lembar Review SK</span>
            </h1>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <a href="{{ route('petinggi.riwayat') }}" class="bg-slate-800 hover:bg-slate-700 text-xs px-3 py-2 rounded-lg text-slate-300 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-clock-rotate-left"></i> <span class="hidden sm:inline">Riwayat</span>
            </a>
            <span class="hidden md:inline-flex bg-amber-500/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-500/30">
                Role: {{ auth()->user()->name ?? 'Petinggi Reviewer' }}
            </span>
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'P', 0, 1)) }}
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Logout</span>
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

    @php
        // Sembunyikan dokumen yang sudah di-ACC (TERIMA) oleh petinggi ini
        // supaya tidak menutupi pengajuan baru yang masih perlu ditinjau.
        $approvals = $approvals->where('status', '!=', 'TERIMA')->values();
    @endphp

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Document Viewer (mengikuti dokumen yang dipilih di kanan) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[60vh] lg:h-[82vh]">
            <!-- Header Doc -->
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                <div>
                    <h2 id="previewTitle" class="font-bold text-slate-900 text-sm">
                        {{ $approvals->first()->document->title ?? 'Belum ada dokumen' }}
                    </h2>
                    <p class="text-xs text-slate-500">
                        No: <span id="previewNumber">{{ $approvals->first()->document->document_number ?? '-' }}</span> • Pengaju:
                        <span id="previewPengaju">{{ $approvals->first()->document->user->name ?? '-' }}</span>
                    </p>
                </div>
                @if($approvals->first())
                <span id="previewVersion" class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded shrink-0">
                    Versi {{ $approvals->first()->document->latestVersion->version_number ?? '1' }}
                </span>
                @endif
            </div>

            <!-- PDF Real Viewer -->
            <div class="flex-1 bg-slate-200 overflow-hidden flex justify-center">
                @if($approvals->first() && $approvals->first()->document->latestVersion)
                    <iframe
                        id="previewFrame"
                        src="{{ asset('storage/' . $approvals->first()->document->latestVersion->file_path) }}"
                        class="w-full h-full border-0"
                        title="Preview Dokumen SK">
                    </iframe>
                @else
                    <div id="previewEmpty" class="flex items-center justify-center h-full text-slate-400 text-sm">
                        <i class="fa-solid fa-file-circle-xmark text-3xl mr-2"></i> Tidak ada berkas untuk ditampilkan.
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Panel Daftar Pengajuan & Form Review -->
        <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 p-5 flex flex-col h-[70vh] lg:h-[82vh] overflow-y-auto">
            <h3 class="font-bold text-white text-sm mb-4 flex items-center gap-2 border-b border-slate-800 pb-3">
                <i class="fa-regular fa-folder-open text-indigo-400"></i> Daftar Persetujuan Dokumen
            </h3>

            <!-- Dynamic Approval List -->
            <div class="space-y-4 flex-1">
                @forelse($approvals as $app)
                    <div class="doc-card p-4 bg-slate-950/60 border rounded-xl space-y-3 cursor-pointer transition {{ $loop->first ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-slate-800' }}"
                        data-id="{{ $app->document_id }}"
                        onclick="selectDocument({{ $app->document_id }})">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $app->document->title }}</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    No: {{ $app->document->document_number }} | Diajukan oleh: {{ $app->document->user->name ?? 'Aspri' }}
                                </p>
                            </div>
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-md uppercase tracking-wider shrink-0
                                {{ $app->status === 'TERIMA' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}
                                {{ $app->status === 'PENDING' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $app->status === 'TOLAK' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : '' }}
                                {{ $app->status === 'MENUNGGU' ? 'bg-slate-800 text-slate-300 border border-slate-700' : '' }}">
                                {{ $app->status }}
                            </span>
                        </div>

                        <!-- Chat Diskusi per Dokumen (Transparan Antar Petinggi & Aspri) -->
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

                        <!-- Form Kirim Pesan Chat (terpisah dari form keputusan review) -->
                        <form action="{{ route('document.comment', $app->document_id) }}" method="POST" class="flex items-center gap-2" onclick="event.stopPropagation()">
                            @csrf
                            <input type="text" name="comment" placeholder="Tulis pesan..." required autocomplete="off"
                                class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                            <button type="submit" class="shrink-0 w-9 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center transition">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </form>

                        <!-- Form Aksi Review Petinggi -->
                        <form action="{{ route('petinggi.review', $app->document_id) }}" method="POST" class="space-y-3 pt-2" onclick="event.stopPropagation()">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Catatan Resmi Keputusan (opsional)</label>
                                <textarea name="comment" rows="2" placeholder="Catatan resmi bersama keputusan Terima/Revisi/Tolak..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1">
                                <button type="button"
                                    onclick="askConfirm(this.closest('form'), 'TERIMA', 'MENYETUJUI dokumen ini')"
                                    class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </button>
                                <button type="button"
                                    onclick="askConfirm(this.closest('form'), 'PENDING', 'meminta REVISI untuk dokumen ini')"
                                    class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Revisi
                                </button>
                                <button type="button"
                                    onclick="askConfirm(this.closest('form'), 'TOLAK', 'MENOLAK dokumen ini')"
                                    class="px-3 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-xmark"></i> Tolak
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-slate-800">
                        <i class="fa-solid fa-file-circle-check text-3xl text-slate-600 mb-2 block"></i>
                        <p class="text-slate-400 text-xs">Belum ada dokumen yang perlu ditinjau saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    @php
        $approvalsForJs = [];
        foreach ($approvals as $app) {
            $approvalsForJs[] = [
                'id' => $app->document_id,
                'title' => $app->document->title,
                'document_number' => $app->document->document_number,
                'pengaju' => $app->document->user->name ?? '-',
                'version_number' => $app->document->latestVersion->version_number ?? '1',
                'file_url' => $app->document->latestVersion ? asset('storage/' . $app->document->latestVersion->file_path) : null,
            ];
        }
    @endphp

    <!-- Script: switch preview kiri sesuai dokumen yang diklik -->
    <script>
        const reviewDocuments = {!! json_encode($approvalsForJs) !!};

        function selectDocument(id) {
            const doc = reviewDocuments.find(d => d.id === id);
            if (!doc) return;

            const titleEl = document.getElementById('previewTitle');
            const numberEl = document.getElementById('previewNumber');
            const pengajuEl = document.getElementById('previewPengaju');
            const versionEl = document.getElementById('previewVersion');
            if (titleEl) titleEl.textContent = doc.title;
            if (numberEl) numberEl.textContent = doc.document_number;
            if (pengajuEl) pengajuEl.textContent = doc.pengaju;
            if (versionEl) versionEl.textContent = 'Versi ' + doc.version_number;

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
    <!-- Modal Konfirmasi Tindakan Review -->
    <div id="confirmModal" class="hidden fixed inset-0 bg-slate-900/70 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-900 mb-1">Konfirmasi Tindakan</h3>
            <p id="confirmText" class="text-sm text-slate-500 mb-5"></p>
            <div class="flex gap-2">
                <button onclick="closeConfirm()" class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
                    Batal
                </button>
                <button onclick="confirmYes()" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    Ya, Lanjutkan
                </button>
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
            if (statusValue === 'TOLAK') {
                text += ' Setelah ditolak, proses review untuk petinggi lain juga akan berhenti.';
            }
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

</body>
</html>