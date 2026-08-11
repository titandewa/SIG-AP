<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGAP_V1 - Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(99,102,241,0.18),_transparent_35%),linear-gradient(135deg,_#eef2ff_0%,_#f8fafc_45%,_#ffffff_100%)] font-sans text-slate-800">
    <div class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-6xl overflow-hidden rounded-[32px] border border-white/70 bg-white/80 shadow-[0_25px_80px_rgba(15,23,42,0.12)] backdrop-blur-xl">
            <div class="grid lg:grid-cols-[1.05fr_0.95fr]">
                <div class="relative hidden lg:block">
                    <div class="h-full min-h-[560px] bg-[linear-gradient(135deg,_rgba(79,70,229,0.96)_0%,_rgba(17,24,39,0.95)_100%)] p-10 text-white">
                        <div class="flex h-full flex-col justify-between">
                            <div>
                                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-sm font-medium backdrop-blur">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    SIGAP_V1 Platform
                                </div>
                                <h1 class="mt-8 text-4xl font-semibold leading-tight">Kelola pengajuan dan approval dokumen dengan lebih tenang.</h1>
                                <p class="mt-4 max-w-md text-sm leading-7 text-indigo-100">Sistem yang membantu tim mengatur dokumen, review, dan pengesahan secara cepat, aman, dan terarah.</p>
                            </div>
                            <div class="rounded-3xl border border-white/15 bg-white/10 p-5 backdrop-blur">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 text-xl">
                                        <i class="fa-solid fa-file-signature"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold">Review SK & TTD digital</p>
                                        <p class="text-xs text-indigo-100">Bekerja lebih cepat dengan alur yang jelas.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full p-6 sm:p-8 lg:p-10">
                    <div class="flex flex-col items-center text-center lg:items-start lg:text-left">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-2xl font-bold text-white shadow-lg shadow-indigo-200">
                            S
                        </div>
                        <h2 class="mt-4 text-2xl font-bold text-slate-900">Masuk ke akun Anda</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Masukkan email dan password untuk melanjutkan ke dashboard Anda.</p>
                    </div>

                    <div class="mt-8 rounded-[24px] border border-slate-200 bg-white/80 p-5 shadow-sm sm:p-6">

            @if (session('status'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="nama@perusahaan.com"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-3 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-11 text-sm text-slate-700 outline-none transition focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600">
                            <i id="togglePasswordIcon" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 text-sm">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <div class="rounded-2xl border border-indigo-100 bg-indigo-50/70 px-3 py-3 space-y-2">
                    <button type="button" id="testNotificationBtn" onclick="window.showLocalNotification()"
                        class="flex items-center gap-2 text-sm font-semibold text-emerald-700 transition hover:text-emerald-800">
                        <i class="fa-solid fa-bell"></i>
                        <span>Tampilkan notifikasi browser</span>
                    </button>
                    <p id="notificationStatus" class="mt-1 text-[11px] leading-5 text-slate-500">Klik untuk meminta izin dan menampilkan notifikasi browser.</p>
                </div>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </button>
            </form>
                    </div>

                    <p class="mt-6 text-center text-[11px] text-slate-400">
                        Akun hanya dibuat oleh admin sistem. Hubungi admin kamu bila ada kendala akses.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src='/app.js'></script>

    <script>
        // Menampilkan notifikasi browser lokal (tanpa FCM)
        window.showLocalNotification = async function () {
            const button = document.getElementById('testNotificationBtn');
            const status = document.getElementById('notificationStatus');

            // Cek dukungan browser
            if (!('Notification' in window)) {
                status.textContent = 'Browser kamu belum mendukung notifikasi.';
                return;
            }

            // Cek apakah menggunakan HTTPS atau localhost
            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                status.textContent = 'Notifikasi web hanya bekerja di HTTPS. Pakai domain HTTPS atau localhost.';
                return;
            }

            button.disabled = true;
            button.querySelector('span').textContent = 'Meminta izin...';

            try {
                // Minta izin notifikasi
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    status.textContent = 'Izin notifikasi ditolak. Aktifkan dari pengaturan browser.';
                    return;
                }

                // Tampilkan notifikasi langsung
                const notification = new Notification('SIGAP - Notifikasi Berhasil', {
                    body: 'Ini adalah notifikasi browser lokal. Push notification FCM sudah terhubung!',
                    icon: '/favicon.ico',
                    badge: '/favicon.ico',
                });

                notification.onclick = function () {
                    window.focus();
                    this.close();
                };

                status.textContent = 'Notifikasi berhasil ditampilkan!';
            } catch (error) {
                console.error(error);
                status.textContent = 'Gagal menampilkan notifikasi. Coba lagi.';
            } finally {
                button.disabled = false;
                button.querySelector('span').textContent = 'Tampilkan notifikasi browser';
            }
        };
    </script>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>