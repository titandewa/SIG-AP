@php
    $roleLabels = [
        'aspri' => 'Administrator',
        'petinggi' => 'Validator',
        'dirut' => 'Direksi',
    ];
@endphp

<x-app-shell title="SIGAP - Manajemen Akun" role="admin" roleColor="indigo" :sidebarItems="[
    ['label' => 'Manajemen Akun', 'url' => route('app.admin.dashboard'), 'icon' => 'fa-solid fa-users-gear'],
]">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg mb-4">
            <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-300 text-rose-800 text-sm px-4 py-3 rounded-lg mb-4">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Manajemen Akun</h1>
            <p class="text-xs text-slate-500">Kelola akun Aspri, Petinggi, dan Dirut.</p>
        </div>
        <button type="button" onclick="openModal('modalTambah')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <i class="fa-solid fa-plus mr-1"></i> Tambah Akun
        </button>
    </div>

    <form method="GET" class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
        <select name="role" class="border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">Semua Role</option>
            <option value="aspri" {{ request('role') === 'aspri' ? 'selected' : '' }}>Administrator</option>
            <option value="petinggi" {{ request('role') === 'petinggi' ? 'selected' : '' }}>Validator</option>
            <option value="dirut" {{ request('role') === 'dirut' ? 'selected' : '' }}>Direksi</option>
        </select>
        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
            <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
        </button>
        @if(request('search') || request('role'))
            <a href="{{ route('app.admin.dashboard') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 px-3 py-2.5">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Nama</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-left px-4 py-3">No WA</th>
                        <th class="text-left px-4 py-3">Role</th>
                        <th class="text-left px-4 py-3">TTD</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($akun as $item)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->email }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-600">{{ $roleLabels[$item->role] ?? $item->role }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($item->signature_path)
                                    <img src="{{ asset('storage/' . $item->signature_path) }}" alt="TTD" class="h-8">
                                @else
                                    <span class="text-slate-400 text-xs">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->force_password_reset)
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-600">Menunggu reset</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-600">Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                        onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ addslashes($item->email) }}', '{{ addslashes($item->phone) }}', '{{ $item->role }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>

                                    <button type="button"
                                        onclick="openTtdModal({{ $item->id }}, '{{ addslashes($item->name) }}')"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition" title="Ubah TTD">
                                        <i class="fa-solid fa-signature text-xs"></i>
                                    </button>

                                    <form action="{{ route('app.admin.akun.reset-password', $item->id) }}" method="POST" onsubmit="return confirm('Reset password {{ addslashes($item->name) }}? User akan diminta membuat password baru saat login berikutnya.')">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 transition" title="Reset Password">
                                            <i class="fa-solid fa-key text-xs"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('app.admin.akun.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus akun {{ addslashes($item->name) }}? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="Hapus">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400 text-sm">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($akun->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $akun->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MODAL TAMBAH AKUN ===================== --}}
    <div id="modalTambah" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Tambah Akun</h3>
                <button type="button" onclick="closeModal('modalTambah')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('app.admin.akun.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Nama</label>
                    <input type="text" name="name" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Email</label>
                    <input type="email" name="email" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">No. WA</label>
                    <input type="text" name="phone" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Role</label>
                    <select name="role" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="aspri">Administrator</option>
                        <option value="petinggi">Validator</option>
                        <option value="dirut">Direksi</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Password</label>
                    <input type="password" name="password" required minlength="8" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition mt-2">Simpan Akun</button>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL EDIT AKUN ===================== --}}
    <div id="modalEdit" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Edit Akun</h3>
                <button type="button" onclick="closeModal('modalEdit')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editForm" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Nama</label>
                    <input type="text" name="name" id="editName" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Email</label>
                    <input type="email" name="email" id="editEmail" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">No. WA</label>
                    <input type="text" name="phone" id="editPhone" class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600 mb-1 block">Role</label>
                    <select name="role" id="editRole" required class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
                        <option value="aspri">Administrator</option>
                        <option value="petinggi">Validator</option>
                        <option value="dirut">Direksi</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition mt-2">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    {{-- ===================== MODAL UBAH TTD (upload saja) ===================== --}}
    <div id="modalTtd" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Ubah TTD — <span id="ttdUserName"></span></h3>
                <button type="button" onclick="closeModal('modalTtd')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <p class="text-[11px] text-slate-400 mb-3">Foto tanda tangan di atas kertas putih polos dengan tinta gelap, hasil terbaik saat pencahayaan rata.</p>

            <input type="file" id="ttdUploadAdmin" accept="image/*" class="block w-full text-sm text-slate-600 border border-slate-300 rounded-lg cursor-pointer file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-indigo-50 file:text-indigo-600 file:font-semibold mb-4">

            <div id="uploadEditorSectionAdmin" class="hidden">
                <div class="mb-4">
                    <label class="flex justify-between text-xs font-semibold text-slate-600 mb-1">
                        <span>Sensitivitas background</span>
                        <span id="thresholdValueAdmin">180</span>
                    </label>
                    <input type="range" id="thresholdSliderAdmin" min="80" max="240" value="180" class="w-full accent-indigo-600">
                </div>
                <div class="border border-slate-200 rounded-xl p-4 bg-[repeating-conic-gradient(#f1f5f9_0%_25%,white_0%_50%)] bg-[length:16px_16px] flex justify-center">
                    <canvas id="uploadPreviewCanvasAdmin" class="max-w-full"></canvas>
                </div>
            </div>

            <form id="ttdForm" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="signature_image" id="signatureImageInputAdmin">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Tanda Tangan
                </button>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/signature-editor.js') }}"></script>
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openEditModal(id, name, email, phone, role) {
            document.getElementById('editForm').action = `{{ url('app/admin/akun') }}/${id}`;
            document.getElementById('editName').value = name;
            document.getElementById('editEmail').value = email;
            document.getElementById('editPhone').value = phone;
            document.getElementById('editRole').value = role;
            openModal('modalEdit');
        }

        SignatureEditor.init('uploadPreviewCanvasAdmin');
        const uploadInputAdmin = document.getElementById('ttdUploadAdmin');
        const uploadEditorSectionAdmin = document.getElementById('uploadEditorSectionAdmin');
        const thresholdSliderAdmin = document.getElementById('thresholdSliderAdmin');
        const thresholdValueAdmin = document.getElementById('thresholdValueAdmin');

        uploadInputAdmin.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            SignatureEditor.loadFile(file, () => {
                SignatureEditor.applyThreshold(parseInt(thresholdSliderAdmin.value));
                uploadEditorSectionAdmin.classList.remove('hidden');
            });
        });

        let sliderDebounceAdmin;
        thresholdSliderAdmin.addEventListener('input', (e) => {
            thresholdValueAdmin.textContent = e.target.value;
            clearTimeout(sliderDebounceAdmin);
            sliderDebounceAdmin = setTimeout(() => SignatureEditor.applyThreshold(parseInt(e.target.value)), 10);
        });

        function openTtdModal(id, name) {
            document.getElementById('ttdForm').action = `{{ url('app/admin/akun') }}/${id}/ttd`;
            document.getElementById('ttdUserName').textContent = name;
            uploadInputAdmin.value = '';
            uploadEditorSectionAdmin.classList.add('hidden');
            openModal('modalTtd');
        }

        document.getElementById('ttdForm').addEventListener('submit', function (e) {
            if (!SignatureEditor.hasImage()) {
                e.preventDefault();
                alert('Silakan upload foto tanda tangan terlebih dahulu.');
                return;
            }
            document.getElementById('signatureImageInputAdmin').value = document.getElementById('uploadPreviewCanvasAdmin').toDataURL('image/png');
        });
    </script>

</x-app-shell>