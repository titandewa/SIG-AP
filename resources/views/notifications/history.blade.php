@php
    $dashboardRoutes = [
        'aspri' => 'app.aspri.dashboard',
        'petinggi' => 'app.petinggi.dashboard',
        'dirut' => 'app.dirut.dashboard',
    ];
    $riwayatRoutes = [
        'aspri' => 'aspri.riwayat',
        'petinggi' => 'petinggi.riwayat',
        'dirut' => 'dirut.riwayat',
    ];
    $userRole = auth()->user()->role ?? 'aspri';
    $dashboardUrl = isset($dashboardRoutes[$userRole]) ? route($dashboardRoutes[$userRole]) : '/';
@endphp

<x-app-shell title="Riwayat Notifikasi" role="{{ $userRole }}" roleColor="indigo" :sidebarItems="[
    ['label' => 'Dashboard', 'url' => $dashboardUrl, 'icon' => 'fa-solid fa-gauge-high'],
    ['label' => 'Riwayat', 'url' => isset($riwayatRoutes[$userRole]) ? route($riwayatRoutes[$userRole]) : $dashboardUrl, 'icon' => 'fa-solid fa-clock-rotate-left'],
]">
    <div class="max-w-5xl mx-auto">
        <div class="rounded-[28px] border border-slate-200 bg-white/90 p-4 shadow-sm sm:p-6 lg:p-8 backdrop-blur">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                        <i class="fa-solid fa-bell"></i>
                        Riwayat notifikasi
                    </div>
                    <h1 class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl">Semua aktivitas notifikasi Anda</h1>
                    <p class="mt-2 text-sm text-slate-500">Pantau dokumen, review, dan pengesahan yang pernah dikirim ke akun Anda.</p>
                </div>
            </div>

            @if($notifications->isEmpty())
                <div class="mt-6 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                    <i class="fa-solid fa-bell-slash text-3xl text-slate-300"></i>
                    <h2 class="mt-3 text-base font-semibold text-slate-700">Belum ada riwayat notifikasi</h2>
                    <p class="mt-2 text-sm text-slate-500">Notifikasi yang dikirim ke akun Anda akan muncul di sini.</p>
                </div>
            @else
                <div class="mt-6 space-y-3">
                    @foreach($notifications as $notification)
                        <a href="{{ $notification->data['url'] ?? '#' }}"
                           onclick="markNotifRead(event, {{ $notification->id }}, '{{ $notification->data['url'] ?? '#' }}')"
                           class="block rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md cursor-pointer">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-indigo-700">
                                            {{ $notification->type ?? 'Info' }}
                                        </span>
                                        <span class="text-[11px] font-medium text-slate-400">{{ $notification->channel }}</span>
                                    </div>
                                    <h2 class="mt-2 text-sm font-semibold text-slate-900">{{ $notification->title }}</h2>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->body }}</p>
                                </div>
                                <div class="shrink-0 text-xs text-slate-400 sm:text-right">
                                    <div class="font-medium text-slate-500">{{ $notification->created_at->translatedFormat('d M Y') }}</div>
                                    <div class="mt-1">{{ $notification->created_at->translatedFormat('H:i') }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6 flex justify-center">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-shell>