<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGAPP - Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.96),_rgba(241,245,249,0.92)_42%,_rgba(226,232,240,0.9)_100%)] font-sans text-slate-800">
    <div class="min-h-screen px-3 py-3 sm:px-5 sm:py-5 lg:px-4 lg:py-4">
        <div class="mx-auto grid min-h-[calc(100vh-1.5rem)] w-full max-w-[1120px] overflow-hidden rounded-[24px] border border-white/70 bg-white shadow-[0_28px_90px_rgba(15,23,42,0.12)] lg:grid-cols-[1.04fr_0.96fr] lg:rounded-[30px]">
            <section class="relative overflow-hidden bg-[linear-gradient(180deg,_#0b2a66_0%,_#0a1d48_48%,_#061437_100%)] px-5 py-7 text-white sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.2),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(37,99,235,0.28),_transparent_34%)]"></div>
                <div class="absolute -bottom-28 right-[-80px] h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>
                <div class="absolute -bottom-32 left-[-40px] h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl"></div>
                <div class="absolute inset-x-0 bottom-0 h-56 bg-[url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 260%22%3E%3Cg fill=%22none%22 stroke=%22rgba(59,130,246,0.28)%22 stroke-width=%221%22%3E%3Cpath d=%22M0 180C120 120 240 290 360 232s240-182 360-128 240 194 360 122 120-34 120-34%22/%3E%3Cpath d=%22M0 208C120 150 240 300 360 250s240-186 360-140 240 170 360 104 120-20 120-20%22/%3E%3Cpath d=%22M0 236C120 180 240 316 360 270s240-186 360-136 240 148 360 86 120-2 120-2%22/%3E%3C/g%3E%3C/svg%3E')] bg-cover bg-bottom bg-no-repeat opacity-70"></div>

                <div class="relative z-10 flex h-full min-h-[360px] flex-col justify-between gap-10 lg:min-h-[660px]">
                    <div>
                        <div class="flex items-center gap-4 sm:gap-5">
                            <div class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-white/6 shadow-[0_16px_40px_rgba(0,0,0,0.18)] ring-1 ring-white/10 backdrop-blur-sm sm:h-24 sm:w-24 sm:rounded-[26px]">
                                <x-sigapp-login-logo variant="mark" class="h-10 w-10 text-white sm:h-16 sm:w-16" />
                            </div>
                            <div>
                                <div class="text-[26px] font-black leading-none tracking-tight sm:text-[42px]"><span class="text-white">SIG</span><span class="text-blue-400">APP</span></div>
                                <div class="mt-2 flex items-center gap-3 text-[9px] font-semibold tracking-[0.18em] text-blue-100/90 sm:gap-4 sm:tracking-[0.28em]">
                                    <span class="h-px w-9 bg-blue-400/80"></span>
                                    SIGN FASTER, WORK BETTER
                                    <span class="h-px w-9 bg-blue-400/80"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 max-w-xl sm:mt-16 lg:mt-24">
                            <h1 class="text-[1.8rem] font-extrabold leading-[1.06] tracking-tight text-white sm:text-[2.15rem] lg:text-5xl">
                                Kelola pengajuan dan
                                <span class="block text-blue-400">approval dokumen</span>
                                dengan lebih tenang.
                            </h1>
                            <p class="mt-4 max-w-lg text-[12px] leading-6 text-slate-200/90 sm:text-sm sm:leading-7 lg:text-base">
                                Sistem yang membantu tim mengatur dokumen, review, dan pengesahan secara cepat, aman, dan terarah.
                            </p>
                        </div>
                    </div>

                    <div class="relative max-w-[380px] rounded-[22px] border border-white/10 bg-white/8 p-4 shadow-[0_18px_50px_rgba(0,0,0,0.14)] backdrop-blur-sm sm:p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500 text-white shadow-lg shadow-blue-500/30">
                                <i class="fa-solid fa-shield-halved text-lg"></i>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-white">Aman &amp; Terlindungi</p>
                                <p class="mt-1 text-sm leading-6 text-slate-200/90">Dokumen dan data Anda terlindungi dengan standar keamanan tertinggi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-[linear-gradient(180deg,_rgba(255,255,255,0.98)_0%,_rgba(250,252,255,0.98)_100%)] px-5 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                <div class="mx-auto flex h-full max-w-[450px] flex-col justify-center">
                    <div class="mb-7 flex items-start gap-4 sm:mb-8">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-[20px] border border-blue-100 bg-white shadow-[0_14px_30px_rgba(37,99,235,0.08)]">
                            <x-sigapp-login-logo variant="mark" class="h-8 w-8 text-blue-600" />
                        </div>
                        <div>
                            <h2 class="text-[1.65rem] font-extrabold tracking-tight text-slate-900 sm:text-[1.95rem]">Masuk ke akun Anda</h2>
                            <p class="mt-2 max-w-md text-sm leading-6 text-slate-500 sm:text-[15px]">Masukkan email dan password untuk melanjutkan ke dashboard SIGAPP.</p>
                        </div>
                    </div>

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
                                <span class="absolute left-4 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-envelope text-[15px]"></i>
                                </span>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Masukkan email SIGAP" class="h-14 w-full rounded-2xl border border-slate-200/90 bg-[linear-gradient(180deg,_#f8fbff_0%,_#edf4ff_100%)] pl-12 pr-4 text-[15px] text-slate-800 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-lock text-[15px]"></i>
                                </span>
                                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="h-14 w-full rounded-2xl border border-slate-200/90 bg-[linear-gradient(180deg,_#f8fbff_0%,_#edf4ff_100%)] pl-12 pr-12 text-[15px] text-slate-800 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan atau sembunyikan password">
                                    <i id="togglePasswordIcon" class="fa-solid fa-eye text-[15px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-600">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Ingat saya</span>
                            </label>

                            <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 transition hover:text-blue-700">Lupa password?</a>
                        </div>

                        <div class="rounded-2xl border border-blue-100 bg-[linear-gradient(180deg,_#f7fbff_0%,_#edf4ff_100%)] px-4 py-4 shadow-[0_10px_24px_rgba(37,99,235,0.06)]">
                            <button type="button" id="testNotificationBtn" onclick="window.showLocalNotification()" class="flex items-center gap-2 text-sm font-bold text-blue-700 transition hover:text-blue-800">
                                <i class="fa-solid fa-bell text-base"></i>
                                <span>Minta notifikasi</span>
                            </button>
                            <p id="notificationStatus" class="mt-2 text-[13px] leading-5 text-slate-500">Izinkan notifikasi agar kamu menerima update penting.</p>
                        </div>

                        <button type="submit" class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(180deg,_#2e69ff_0%,_#114ad8_100%)] px-4 text-base font-bold text-white shadow-[0_16px_35px_rgba(37,99,235,0.35)] transition hover:brightness-105 focus:outline-none focus:ring-4 focus:ring-blue-200">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Masuk
                        </button>
                    </form>

                    <div class="my-6 flex items-center gap-4 text-slate-300 sm:my-7">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span class="text-sm text-slate-400">Akun hanya dibuat oleh admin sistem.</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <p class="flex flex-wrap items-center justify-center gap-2 text-center text-sm text-slate-500">
                        <i class="fa-solid fa-headset text-slate-400"></i>
                        Butuh bantuan?
                        <a href="mailto:admin@sigapp.local" class="font-semibold text-blue-700 transition hover:text-blue-800">Hubungi Admin Sistem</a>
                    </p>
                </div>
            </section>
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