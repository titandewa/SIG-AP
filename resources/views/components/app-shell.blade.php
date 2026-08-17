@props(['title' => 'SIGAP', 'role' => 'user', 'roleColor' => 'indigo', 'sidebarItems' => []])

@php
    $notifications = auth()->check()
        ? auth()->user()->notificationHistories()->whereNull('read_at')->latest()->take(5)->get()
        : collect();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">
    <title>{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
</head>
<body class="overflow-x-hidden bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.95),_rgba(241,245,249,0.95))] min-h-screen text-slate-800">
    <div class="min-h-screen flex flex-col">
        <nav class="bg-[#021b41] text-white px-3 sm:px-6 py-3 flex items-center justify-between gap-2 shadow-lg shrink-0 sticky top-0 z-30 border-b border-white/10 backdrop-blur">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <a class="flex items-center shrink-0">
                    <img src="{{ asset('images/logo-banner.png') }}" alt="SIGAP_V1" class="h-10 sm:h-12 w-auto rounded-xl object-contain">
                </a>

                <!-- Tab Navigasi -->
                <div class="hidden sm:flex items-center gap-1">
                    @foreach($sidebarItems as $item)
                        @php
                            $isActive = request()->url() === url($item['url'] ?? '#');
                        @endphp
                        <a href="{{ $item['url'] ?? '#' }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition
                                {{ $isActive
                                    ? 'bg-white text-[#021b41] shadow-sm'
                                    : 'bg-white/10 text-white/80 hover:bg-white/20 hover:text-white' }}">
                            <i class="{{ $item['icon'] ?? 'fa-solid fa-circle' }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @auth
                @php
                    $onNotifPage = request()->routeIs('notifications.history');
                @endphp
                <div class="relative" id="notifDropdownWrapper">
                    @if($onNotifPage)
                        <span class="bg-white text-indigo-900 px-2.5 sm:px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-bell"></i>
                            <span class="hidden md:inline">Notifikasi</span>
                        </span>
                    @else
                        <button type="button" id="notifDropdownButton" class="bg-[#021b41] hover:bg-[#03265c] px-2.5 sm:px-3 py-2 rounded-2xl text-xs sm:text-sm font-semibold flex items-center gap-2 transition shadow-sm border border-white/10">
                            <div class="relative">
                                <i class="fa-solid fa-bell"></i>
                                @if($notifications->isNotEmpty())
                                    <span class="absolute -top-2 -right-2 min-w-5 h-5 px-1 rounded-full bg-rose-500 text-[10px] font-bold flex items-center justify-center">{{ $notifications->count() }}</span>
                                @endif
                            </div>
                            <span class="hidden md:inline">Notifikasi</span>
                        </button>
                    @endif

                    <div id="notifDropdownMenu" class="hidden fixed left-3 right-3 top-16 sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-[22rem] rounded-2xl border border-slate-200 bg-white shadow-2xl overflow-hidden z-50">
                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Notifikasi terbaru</p>
                                <p class="text-xs text-slate-500">Terakhir diperbarui</p>
                            </div>
                            <a href="{{ route('notifications.history') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat semua</a>
                        </div>

                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                            @forelse($notifications as $notification)
                                <a href="{{ $notification->data['url'] ?? route('notifications.history') }}"
                                    onclick="markNotifRead(event, {{ $notification->id }}, '{{ $notification->data['url'] ?? route('notifications.history') }}')"
                                    class="block px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-1.5 h-2.5 w-2.5 rounded-full bg-indigo-500 shrink-0"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $notification->title }}</p>
                                            <p class="text-xs text-slate-500 mt-1">{{ $notification->body }}</p>
                                            <p class="text-[11px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-6 text-center">
                                    <i class="fa-solid fa-bell-slash text-slate-300 text-xl"></i>
                                    <p class="text-sm text-slate-500 mt-2">Belum ada notifikasi</p>
                                </div>
                            @endforelse
                        </div>

                        @if($notifications->isNotEmpty())
                            <form method="POST" action="{{ route('notifications.clear') }}" class="border-t border-slate-200">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                    Hapus semua notifikasi
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endauth

                @auth
                <div class="w-9 h-9 rounded-full bg-[#021b41] flex items-center justify-center font-bold text-sm shrink-0 border border-white/10">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                @endauth

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="bg-[#021b41] hover:bg-[#03265c] p-2.5 rounded-2xl text-sm font-semibold transition shadow-sm border border-white/10">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Tab Navigasi versi mobile -->
        <div class="sm:hidden flex items-center gap-1 px-3 py-2 bg-[#021b41] overflow-x-auto">
            @foreach($sidebarItems as $item)
                @php
                    $isActive = request()->url() === url($item['url'] ?? '#');
                @endphp
                <a href="{{ $item['url'] ?? '#' }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition whitespace-nowrap
                        {{ $isActive
                            ? 'bg-white text-[#021b41]'
                            : 'bg-white/10 text-white/80 hover:bg-white/20' }}">
                    <i class="{{ $item['icon'] ?? 'fa-solid fa-circle' }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <main class="flex-1 p-3 sm:p-5 lg:p-8">
            {{ $slot }}
        </main>
    </div>

    <script>
        function markNotifRead(event, id, url) {
            event.preventDefault();
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).finally(() => {
                window.location.href = url;
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('notifDropdownWrapper');
            const button = document.getElementById('notifDropdownButton');
            const menu = document.getElementById('notifDropdownMenu');

            if (wrapper && button && menu) {
                button.addEventListener('click', function (event) {
                    event.stopPropagation();
                    menu.classList.toggle('hidden');
                });

                document.addEventListener('click', function (event) {
                    if (wrapper && !wrapper.contains(event.target)) {
                        menu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>