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
    <nav class="bg-indigo-900 text-white px-4 sm:px-6 py-3 sm:py-4 flex flex-wrap justify-between items-center gap-y-2 shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('app.aspri.dashboard') }}" class="bg-indigo-800 hover:bg-indigo-700 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> <span class="hidden sm:inline">Kembali</span>
            </a>
            <h1 class="font-bold text-base sm:text-lg leading-none border-l border-indigo-800 pl-3">
                SIGAP_V1 <span class="text-xs text-indigo-300 font-normal hidden sm:inline">/ Riwayat Dokumen</span>
            </h1>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <span class="hidden md:inline-flex bg-indigo-800 text-xs px-3 py-1 rounded-full border border-indigo-700">Role: Aspri / Admin</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-4 sm:p-6">

        <!-- Filter -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" action="{{ route('aspri.riwayat') }}" class="flex flex-wrap items-center gap-3">
                <label class="text-xs font-semibold text-slate-600">Filter Status:</label>
                <select name="status" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
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
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                        <div>
                            <span class="font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider
                                {{ $document->status === 'DISETUJUI' ? 'bg-emerald-100 text-emerald-800' : '' }}
                                {{ $document->status === 'DITOLAK' ? 'bg-rose-100 text-rose-800' : '' }}">
                                {{ $document->status }}
                            </span>
                            <h3 class="font-bold text-slate-800 text-base mt-1">{{ $document->title }}</h3>
                            <p class="text-xs text-slate-500">
                                No: {{ $document->document_number }} • Diajukan: {{ $document->created_at->translatedFormat('d F Y, H:i') }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $document->status === 'DISETUJUI' ? 'Disahkan' : 'Ditolak' }}: {{ $document->updated_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    @if($document->status === 'DISETUJUI' && $document->signed_file_path)
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <a href="{{ asset('storage/' . $document->signed_file_path) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                                <i class="fa-solid fa-download"></i> Download Dokumen Bertandatangan
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2 block"></i>
                    <p class="text-slate-400 text-sm">Belum ada dokumen yang selesai diproses.</p>
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>