<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Riwayat Pengesahan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-indigo-950 text-white px-4 sm:px-6 py-3 sm:py-4 flex flex-wrap justify-between items-center gap-y-2 shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('app.dirut.dashboard') }}" class="bg-indigo-900 hover:bg-indigo-800 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> <span class="hidden sm:inline">Kembali</span>
            </a>
            <h1 class="font-bold text-base sm:text-lg leading-none border-l border-indigo-800 pl-3">
                SIGAP_V1 <span class="text-xs text-amber-400 font-normal hidden sm:inline">/ Riwayat Pengesahan</span>
            </h1>
        </div>
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <span class="hidden md:inline-flex bg-amber-400/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-400/30 font-semibold">
                Role: {{ auth()->user()->name ?? 'Direktur Utama (Dirut)' }}
            </span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-800 hover:bg-rose-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="hidden sm:inline">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-4 sm:p-6">

        <p class="text-xs text-slate-500 mb-4">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Hanya menampilkan dokumen yang sudah kamu sahkan/tandatangani.
        </p>

        <!-- List Dokumen yang Sudah Disahkan -->
        <div class="space-y-4">
            @forelse($documents as $document)
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                DISETUJUI
                            </span>
                            <h3 class="font-bold text-slate-800 text-base mt-1">{{ $document->title }}</h3>
                            <p class="text-xs text-slate-500">
                                No: {{ $document->document_number }} • Pengaju: {{ $document->user->name ?? '-' }}
                                • Disahkan: {{ $document->updated_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    @if($document->signed_file_path)
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
                    <p class="text-slate-400 text-sm">Belum ada dokumen yang kamu sahkan.</p>
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>