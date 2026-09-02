<x-app-shell title="SIGAP - Riwayat Pengesahan" role="dirut" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.dirut.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('dirut.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Atur TTD', 'url' => route('dirut.signature.page'), 'icon' => 'fa-solid fa-signature'],
]">

    <div class="max-w-5xl mx-auto">

        <p class="text-xs text-slate-500 mb-4">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Hanya menampilkan dokumen yang sudah kamu sahkan/tandatangani.
        </p>

        <!-- Search -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" action="{{ route('dirut.riwayat') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau nomor SK..."
                    class="text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-indigo-500 flex-1 min-w-[180px]">

                <button type="submit" class="text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg transition">
                    Cari
                </button>

                @if(request('search'))
                    <a href="{{ route('dirut.riwayat') }}" class="text-xs text-rose-600 hover:underline">Reset filter</a>
                @endif
            </form>
        </div>

        <!-- List Dokumen yang Sudah Disahkan -->
        <div class="space-y-4">
            @forelse($documents as $document)
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <x-status-badge status="DISETUJUI" />
                            <h3 class="font-bold text-slate-800 text-base mt-1">{{ $document->title }}</h3>
                            <p class="text-xs text-slate-500">
                                No: {{ $document->document_number }} • Administrator: {{ $document->user->name ?? '-' }}
                                • Disahkan: {{ $document->updated_at->translatedFormat('d F Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    @if($document->signed_file_path)
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
                    <p class="text-slate-400 text-sm">Belum ada dokumen yang kamu sahkan.</p>
                </div>
            @endforelse
        </div>
    </div>

</x-app-shell>