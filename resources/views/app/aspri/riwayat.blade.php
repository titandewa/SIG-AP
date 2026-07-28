<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Riwayat Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-indigo-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('app.aspri.dashboard') }}" class="bg-indigo-800 hover:bg-indigo-700 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h1 class="font-bold text-lg leading-none border-l border-indigo-800 pl-3">
                SIGAP_V1 <span class="text-xs text-indigo-300 font-normal">/ Riwayat Dokumen</span>
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-indigo-800 text-xs px-3 py-1 rounded-full border border-indigo-700">Role: Aspri / Admin</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-6">

        <!-- Filter -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" action="{{ route('aspri.riwayat') }}" class="flex items-center gap-3">
                <label class="text-xs font-semibold text-slate-600">Filter Status:</label>
                <select name="status" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="DITINJAU" {{ request('status') === 'DITINJAU' ? 'selected' : '' }}>Ditinjau</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending / Revisi</option>
                    <option value="SIAP_TTD" {{ request('status') === 'SIAP_TTD' ? 'selected' : '' }}>Siap TTD</option>
                    <option value="DISETUJUI" {{ request('status') === 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                    <option value="DITOLAK" {{ request('status') === 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                </select>
                @if(request('status'))
                    <a href="{{ route('aspri.riwayat') }}" class="text-xs text-rose-600 hover:underline">Reset filter</a>
                @endif
            </form>
        </div>

        <!-- List Dokumen -->
        <div class="space-y-4">
            @forelse($documents as $document)
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider
                                {{ $document->status === 'DITINJAU' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $document->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : '' }}
                                {{ $document->status === 'SIAP_TTD' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $document->status === 'DISETUJUI' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $document->status === 'DITOLAK' ? 'bg-rose-100 text-rose-800' : '' }}">
                                {{ $document->status }}
                            </span>
                            <h3 class="font-bold text-slate-800 text-base mt-1">{{ $document->title }}</h3>
                            <p class="text-xs text-slate-500">
                                No: {{ $document->document_number }} • Diajukan: {{ $document->created_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>

                        @if(in_array($document->status, ['DITINJAU', 'PENDING']))
                            <div class="text-right countdown-box" data-created="{{ $document->created_at->toIso8601String() }}">
                                <p class="text-[10px] text-slate-400 mb-1">Sisa Waktu Batas 24 Jam</p>
                                <p class="countdown-text font-bold text-sm text-slate-700">-</p>
                            </div>
                        @endif
                    </div>

                    @if($document->status === 'DISETUJUI' && $document->signed_file_path)
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <a href="{{ asset('storage/' . $document->signed_file_path) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                                <i class="fa-solid fa-download"></i> Download Sertifikat Pengesahan
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2 block"></i>
                    <p class="text-slate-400 text-sm">Tidak ada dokumen untuk filter ini.</p>
                </div>
            @endforelse
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

                if (diff <= 0) {
                    textEl.textContent = 'Terlambat';
                    textEl.className = 'countdown-text font-bold text-sm text-rose-600';
                } else {
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    textEl.textContent = `${hours}j ${minutes}m ${seconds}d`;
                    textEl.className = 'countdown-text font-bold text-sm ' + (hours < 3 ? 'text-rose-600' : 'text-slate-700');
                }
            });
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    </script>

</body>
</html>