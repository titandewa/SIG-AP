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
    <nav class="bg-slate-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('app.aspri.dashboard') }}" class="bg-slate-800 hover:bg-slate-700 p-2 rounded-lg text-xs font-semibold text-slate-300 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h1 class="font-bold text-lg leading-none border-l border-slate-700 pl-3">
                SIGAP_V1 <span class="text-xs text-indigo-400 font-normal">/ Lembar Review SK</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-amber-500/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-500/30">
                Role: {{ auth()->user()->name ?? 'Petinggi Reviewer' }}
            </span>
            <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center font-bold">
                {{ strtoupper(substr(auth()->user()->name ?? 'P', 0, 1)) }}
            </div>

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

    <!-- Content Area -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Document Viewer (mengikuti dokumen yang dipilih di kanan) -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col h-[82vh]">
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
        <div class="bg-slate-900 rounded-2xl shadow-sm border border-slate-800 p-5 flex flex-col h-[82vh] overflow-y-auto">
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

                        <!-- Riwayat Komentar (Transparan Antar Petinggi) -->
                        @if($app->document->comments->count() > 0)
                            <div class="bg-slate-800/60 rounded-lg p-3 space-y-2 max-h-40 overflow-y-auto">
                                <p class="text-[10px] font-semibold text-slate-400 uppercase mb-1">Riwayat Komentar</p>
                                @foreach($app->document->comments as $comment)
                                    <div class="text-[11px] border-l-2 border-indigo-500 pl-2">
                                        <p class="text-slate-200">{{ $comment->comment }}</p>
                                        <p class="text-slate-500 mt-0.5">
                                            — {{ $comment->user->name ?? 'Petinggi' }} • {{ $comment->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Form Aksi Review Petinggi -->
                        <form action="{{ route('petinggi.review', $app->document_id) }}" method="POST" class="space-y-3 pt-2" onclick="event.stopPropagation()">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Catatan / Komentar Review</label>
                                <textarea name="comment" rows="3" placeholder="Tulis catatan/catatan revisi jika ada..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition resize-none"></textarea>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-1">
                                <button type="submit" name="status" value="TERIMA" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </button>
                                <button type="submit" name="status" value="PENDING" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1 shadow-sm">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Revisi
                                </button>
                                <button type="submit" name="status" value="TOLAK" class="px-3 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-lg transition text-center flex items-center justify-center gap-1 shadow-sm">
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

</body>
</html>