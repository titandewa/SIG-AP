<x-app-shell title="SIGAP - Detail Dokumen" role="aspri" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => route('app.aspri.dashboard'), 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => route('aspri.riwayat'), 'icon' => 'fa-solid fa-clock-rotate-left'],
]">

    <div class="max-w-3xl mx-auto">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg mb-4">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-300 text-rose-800 text-sm px-4 py-3 rounded-lg mb-4">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Info Dokumen -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                <div>
                    <span class="font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-wider
                        {{ $document->status === 'DITINJAU' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $document->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $document->status === 'SIAP_TTD' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $document->status === 'DISETUJUI' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $document->status === 'DITOLAK' ? 'bg-rose-100 text-rose-800' : '' }}">
                        {{ $document->status }}
                    </span>
                    <h2 class="font-bold text-slate-900 text-lg mt-1">{{ $document->title }}</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        No: {{ $document->document_number }} •
                        Diajukan: {{ $document->created_at->translatedFormat('d F Y, H:i') }}
                        @if($document->latestVersion)
                            <br>File: {{ $document->latestVersion->original_filename }}
                        @endif
                    </p>
                </div>

                @if($document->latestVersion)
                    <a href="{{ asset('storage/' . $document->latestVersion->file_path) }}" target="_blank"
                        class="text-xs font-semibold text-indigo-600 bg-indigo-50 border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition shrink-0">
                        <i class="fa-solid fa-file-pdf mr-1"></i> Lihat PDF
                    </a>
                @endif
            </div>

            @if($document->status === 'DISETUJUI' && $document->signed_file_path)
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <a href="{{ asset('storage/' . $document->signed_file_path) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg hover:bg-emerald-100 transition">
                        <i class="fa-solid fa-download"></i> Download Sertifikat Pengesahan
                    </a>
                </div>
            @endif

            <!-- Baris Tombol Aksi -->
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-2">
                @if(in_array($document->status, ['DITINJAU', 'PENDING']))
                    <form action="{{ route('aspri.reminder', $document->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition shadow-sm flex items-center gap-1.5">
                            <i class="fa-brands fa-whatsapp text-sm"></i> Send Auto-WA Reminder
                        </button>
                    </form>
                @endif

                <a href="{{ route('aspri.version-history', $document->id) }}"
                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Versi
                </a>

                @if($document->status !== 'DISETUJUI')
                    <form action="{{ route('aspri.destroy', $document->id) }}" method="POST"
                        onsubmit="return confirm('Yakin hapus pengajuan ini? Tindakan tidak bisa dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-rose-100 hover:bg-rose-200 text-rose-700 text-xs font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5">
                            <i class="fa-solid fa-trash"></i> Hapus Pengajuan
                        </button>
                    </form>
                @endif
            </div>

            <!-- Form Upload Revisi & Ganti Judul: hanya muncul kalau status PENDING (diminta revisi) -->
            @if($document->status === 'PENDING')
                <div class="mt-4 pt-4 border-t border-amber-200 bg-amber-50/50 -mx-5 px-5 pb-3 rounded-b-2xl">
                    <p class="text-xs text-amber-700 font-semibold mb-3 flex items-center gap-1.5">
                        <i class="fa-solid fa-triangle-exclamation"></i> Validator meminta revisi. Perbarui judul dan berkas terbaru:
                    </p>
                    <form action="{{ route('aspri.revisi', $document->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Judul / Perihal SK Baru</label>
                            <input type="text" name="title" value="{{ old('title', $document->title) }}" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg bg-white focus:ring-2 focus:ring-amber-500 outline-none" required>
                            @error('title')
                                <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Upload Berkas PDF Baru (.pdf)</label>
                            <input type="file" name="pdf_file" accept=".pdf" required
                                class="text-xs w-full bg-white border border-slate-300 rounded-lg file:mr-2 file:py-2 file:px-3 file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-700 file:cursor-pointer">
                            @error('pdf_file')
                                <p class="text-[10px] text-rose-600 mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold py-2.5 rounded-lg transition shadow-sm flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-upload"></i> Kirim Revisi & Perbarui Judul
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        <!-- Status Approval -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-4">
            <p class="font-semibold text-slate-700 text-sm mb-3">Status Persetujuan Validator</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                @foreach($document->approvals as $approval)
                    <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-lg border
                        {{ $approval->status === 'MENUNGGU' ? 'border-amber-300 bg-amber-50/30' : 'border-slate-200' }}">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full
                                {{ $approval->status === 'TERIMA' ? 'bg-emerald-500' : '' }}
                                {{ $approval->status === 'MENUNGGU' ? 'bg-amber-500 animate-pulse' : '' }}
                                {{ $approval->status === 'PENDING' ? 'bg-amber-500' : '' }}
                                {{ $approval->status === 'TOLAK' ? 'bg-rose-500' : '' }}">
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">{{ $approval->user->name ?? 'Validator' }}</p>
                                <p class="text-[10px] {{ $approval->status === 'MENUNGGU' ? 'text-amber-600 font-medium' : 'text-slate-400' }}">
                                    {{ $approval->status === 'MENUNGGU' ? 'Belum Menanggapi' : 'Terakhir update: '.$approval->updated_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <span class="font-bold text-[10px] px-2 py-0.5 rounded
                            {{ $approval->status === 'TERIMA' ? 'bg-emerald-50 text-emerald-700' : '' }}
                            {{ $approval->status === 'MENUNGGU' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $approval->status === 'PENDING' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $approval->status === 'TOLAK' ? 'bg-rose-100 text-rose-800' : '' }}">
                            {{ $approval->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chat Diskusi Dokumen -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
            <p class="font-semibold text-slate-700 text-sm mb-3 flex items-center gap-2">
                <i class="fa-regular fa-comments text-indigo-500"></i> Diskusi Dokumen
            </p>

            <div class="max-h-96 overflow-y-auto mb-4 pr-1">
                @forelse($document->comments as $comment)
                    @php $isMine = $comment->user_id === auth()->id(); @endphp
                    <div class="flex gap-2 mb-4 {{ $isMine ? 'flex-row-reverse' : '' }}">
                        <div class="w-8 h-8 rounded-full {{ $isMine ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-700' }} flex items-center justify-center font-bold text-xs shrink-0">
                            {{ strtoupper(substr($comment->user->name ?? 'P', 0, 1)) }}
                        </div>
                        <div class="max-w-[75%] {{ $isMine ? 'bg-indigo-600 text-white rounded-xl rounded-tr-none' : 'bg-slate-50 border border-slate-200 text-slate-700 rounded-xl rounded-tl-none' }} p-3">
                            <div class="flex justify-between items-center gap-3 mb-1">
                                <p class="font-semibold text-xs {{ $isMine ? 'text-indigo-100' : 'text-slate-800' }}">
                                    {{ $isMine ? 'Kamu' : ($comment->user->name ?? 'Validator') }}
                                </p>
                                <p class="text-[10px] {{ $isMine ? 'text-indigo-200' : 'text-slate-400' }} shrink-0">
                                    {{ $comment->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <p class="text-sm">{{ $comment->comment }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-sm">
                        <i class="fa-regular fa-comment-dots text-2xl mb-2 block"></i>
                        Belum ada diskusi untuk dokumen ini.
                    </div>
                @endforelse
            </div>

            <!-- Form Kirim Pesan -->
            <form action="{{ route('document.comment', $document->id) }}" method="POST" class="flex items-center gap-2 pt-3 border-t border-slate-100">
                @csrf
                <input type="text" name="comment" placeholder="Tulis pesan ke Validator..." required autocomplete="off"
                    class="flex-1 border border-slate-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <button type="submit" class="shrink-0 w-10 h-10 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition">
                    <i class="fa-solid fa-paper-plane text-sm"></i>
                </button>
            </form>
        </div>

    </div>

</x-app-shell>