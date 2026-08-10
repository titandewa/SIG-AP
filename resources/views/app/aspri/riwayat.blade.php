<x-app-shell title="SIGAP - Riwayat Dokumen" role="aspri" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.aspri.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('aspri.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
]">

    <div class="max-w-5xl mx-auto">

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

</x-app-shell>