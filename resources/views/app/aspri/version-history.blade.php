<x-app-shell title="SIGAP - Riwayat Versi" role="aspri" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.aspri.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('aspri.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
]">

    <div class="max-w-2xl mx-auto">

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <h2 class="font-bold text-slate-900 text-sm">{{ $document->title }}</h2>
            <p class="text-xs text-slate-500">No: {{ $document->document_number }} • {{ $versions->count() }} versi tersimpan</p>
        </div>

        <!-- Timeline mirip commit history -->
        <div class="relative pl-6">
            <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-slate-200"></div>

            @forelse($versions as $version)
                <div class="relative mb-6">
                    <div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2
                        {{ $loop->first ? 'bg-indigo-600 border-indigo-600' : 'bg-white border-slate-300' }}"></div>

                    <a href="{{ asset('storage/' . $version->file_path) }}" target="_blank"
                        class="block bg-white p-4 rounded-xl border border-slate-200 hover:border-indigo-300 hover:shadow-sm transition">
                        <div class="flex justify-between items-start gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded
                                        {{ $loop->first ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">
                                        v{{ $version->version_number }}
                                    </span>
                                    @if($loop->first)
                                        <span class="text-[10px] font-semibold text-emerald-600">● Versi terbaru</span>
                                    @endif
                                </div>
                                <p class="font-semibold text-slate-800 text-sm mt-1.5 truncate">
                                    <i class="fa-solid fa-file-pdf text-rose-500 mr-1"></i> {{ $version->original_filename }}
                                </p>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Diunggah {{ $version->created_at->translatedFormat('d F Y, H:i') }} • {{ $version->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-up-right-from-square text-slate-300 shrink-0 mt-1"></i>
                        </div>
                    </a>
                </div>
            @empty
                <p class="text-slate-400 text-sm">Belum ada riwayat versi.</p>
            @endforelse
        </div>
    </div>

</x-app-shell>