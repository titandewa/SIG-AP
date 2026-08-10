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