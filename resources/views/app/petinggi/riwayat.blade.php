<x-app-shell title="SIGAP - Riwayat Review Saya" role="petinggi" roleColor="slate" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.petinggi.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('petinggi.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Atur TTD', 'url' => route('petinggi.signature.page'), 'icon' => 'fa-solid fa-signature'],
]">

    <div class="max-w-5xl mx-auto">

        <p class="text-xs text-slate-500 mb-4 -mt-2">
            <i class="fa-solid fa-circle-info mr-1"></i>
            Hanya menampilkan dokumen yang sudah selesai kamu putuskan (diterima/ditolak/dibatalkan).
        </p>

        <!-- Filter -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" action="{{ route('petinggi.riwayat') }}" class="flex flex-wrap items-center gap-3">
                <label class="text-xs font-semibold text-slate-600">Filter Status:</label>
                <select name="status" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="TERIMA" {{ request('status') === 'TERIMA' ? 'selected' : '' }}>Diterima</option>
                    <option value="TOLAK" {{ request('status') === 'TOLAK' ? 'selected' : '' }}>Ditolak</option>
                    <option value="DIBATALKAN" {{ request('status') === 'DIBATALKAN' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau nomor SK..."
                    class="text-xs border border-slate-300 rounded-lg px-3 py-2 outline-none focus:border-indigo-500 flex-1 min-w-[180px]">

                <button type="submit" class="text-xs font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg transition">
                    Cari
                </button>

                @if(request('status') || request('search'))
                <a href="{{ route('petinggi.riwayat') }}" class="text-xs text-rose-600 hover:underline">Reset filter</a>
                @endif
            </form>
        </div>

        <!-- List Riwayat Approval Milik Petinggi Ini -->
        <div class="space-y-4">
            @forelse($approvals as $app)
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                    <div>
                        <x-status-badge :status="$app->document->display_status" />
                        <h3 class="font-bold text-slate-800 text-base mt-1">{{ $app->document->title }}</h3>
                        <p class="text-xs text-slate-500">
                            No: {{ $app->document->document_number }} • Administrator: {{ $app->document->user->name ?? '-' }}
                            • Diajukan: {{ $app->document->created_at->translatedFormat('d F Y, H:i') }}
                        </p>
                        @if($app->document->pending_parties_label)
                        <p class="text-[11px] text-slate-400 mt-1">
                            <i class="fa-solid fa-hourglass-half mr-1"></i>{{ $app->document->pending_parties_label }}
                        </p>
                        @endif
                    </div>

                    <p class="text-[11px] text-slate-400 shrink-0">
                        Ditinjau: {{ $app->updated_at->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>

                @if($app->status === 'DIBATALKAN')
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <p class="text-xs text-slate-500 italic">Dibatalkan karena dokumen ditolak oleh petinggi lain.</p>
                </div>
                @endif

                @php
                $myComment = $app->document->comments
                ->where('user_id', auth()->id())
                ->last();
                @endphp
                @if($myComment)
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase mb-1">Catatan Kamu</p>
                    <p class="text-xs text-slate-600 italic">"{{ $myComment->comment }}"</p>
                </div>
                @endif

                @if($app->document->status === 'DITOLAK')
                @php
                $penolak = $app->document->approvals->where('status', 'TOLAK');
                @endphp
                @if($penolak->count())
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <p class="text-[10px] font-semibold text-slate-400 uppercase mb-1">Ditolak Oleh</p>
                    @foreach($penolak as $p)
                    <p class="text-xs text-rose-600">{{ $p->user->name ?? '-' }}</p>
                    @endforeach
                </div>
                @endif

                @if($app->document->latestVersion)
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ asset('storage/' . $app->document->latestVersion->file_path) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                        <i class="fa-solid fa-file-pdf"></i> Lihat PDF
                    </a>
                </div>
                @endif
                @endif

                @if($app->document->status === 'DISETUJUI' && $app->document->signed_file_path)
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('document.download', [$document->id, 'signed']) }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                        <i class="fa-solid fa-download"></i> Download Dokumen Bertandatangan
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
                <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2 block"></i>
                <p class="text-slate-400 text-sm">Tidak ada riwayat review untuk filter ini.</p>
            </div>
            @endforelse
        </div>
    </div>

</x-app-shell>