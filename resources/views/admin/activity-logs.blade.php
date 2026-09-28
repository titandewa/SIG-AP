@php
    $roleLabels = [
        'aspri' => 'Administrator',
        'petinggi' => 'Validator',
        'dirut' => 'Direksi',
        'admin' => 'Admin',
    ];

    $actionLabels = [
        'kirim_sk' => 'Kirim SK',
        'setuju' => 'Setuju',
        'revisi' => 'Revisi',
        'tanda_tangan' => 'Tanda Tangan',
        'sahkan_tanpa_ttd' => 'Sahkan Tanpa TTD',
        'buat_akun' => 'Buat Akun',
        'edit_akun' => 'Edit Akun',
        'hapus_akun' => 'Hapus Akun',
        'reset_password' => 'Reset Password',
        'update_ttd' => 'Update TTD',
    ];

    $actionColors = [
        'kirim_sk' => 'sky',
        'setuju' => 'emerald',
        'revisi' => 'amber',
        'tanda_tangan' => 'indigo',
        'sahkan_tanpa_ttd' => 'violet',
        'buat_akun' => 'emerald',
        'edit_akun' => 'sky',
        'hapus_akun' => 'rose',
        'reset_password' => 'amber',
        'update_ttd' => 'indigo',
    ];
@endphp

<x-app-shell title="SIGAP - Log Aktivitas" role="admin" roleColor="indigo" :sidebarItems="[
    ['label' => 'Manajemen Akun', 'url' => route('app.admin.dashboard'), 'icon' => 'fa-solid fa-users-gear'],
    ['label' => 'Log Aktivitas', 'url' => route('app.admin.activity-logs'), 'icon' => 'fa-solid fa-clock-rotate-left'],
]">

    @if(session('status'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg mb-4">
            <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Log Aktivitas</h1>
            <p class="text-xs text-slate-500">Riwayat aktivitas seluruh pengguna di sistem.</p>
        </div>
    </div>

    <form method="GET" class="bg-white border border-slate-200 rounded-2xl p-4 mb-4 flex flex-col lg:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau deskripsi..." class="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">

        <select name="role" class="border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">Semua Role</option>
            @foreach($roleLabels as $value => $label)
                <option value="{{ $value }}" {{ request('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <select name="action" class="border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
            <option value="">Semua Aksi</option>
            @foreach($actionLabels as $value => $label)
                <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-slate-300 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-200">

        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition whitespace-nowrap">
            <i class="fa-solid fa-magnifying-glass mr-1"></i> Filter
        </button>

        @if(request()->anyFilled(['search', 'role', 'action', 'date_from', 'date_to']))
            <a href="{{ route('app.admin.activity-logs') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700 px-3 py-2.5 whitespace-nowrap">Reset</a>
        @endif
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Waktu</th>
                        <th class="text-left px-4 py-3">Aktor</th>
                        <th class="text-left px-4 py-3">Role</th>
                        <th class="text-left px-4 py-3">Aksi</th>
                        <th class="text-left px-4 py-3">Deskripsi</th>
                        <th class="text-left px-4 py-3">Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->actor->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-600">
                                    {{ $roleLabels[$log->actor_role] ?? $log->actor_role }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php $color = $actionColors[$log->action] ?? 'slate'; @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-{{ $color }}-50 text-{{ $color }}-600">
                                    {{ $actionLabels[$log->action] ?? $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-md">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ $log->document->title ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">Belum ada aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</x-app-shell>