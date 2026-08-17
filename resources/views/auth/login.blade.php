<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#000000">
    <title>SIGAPP - Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sigDark: '#0D1B3D',
                        sigBlue: '#4DA8FF',
                    }
                }
            }
        }
    </script>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.96),_rgba(241,245,249,0.92)_42%,_rgba(226,232,240,0.9)_100%)] font-sans text-slate-800">
    <div class="min-h-screen px-3 py-3 sm:px-5 sm:py-5 lg:px-4 lg:py-4">
        <div class="mx-auto grid min-h-[calc(100vh-1.5rem)] w-full max-w-[1120px] overflow-hidden rounded-[24px] border border-white/70 bg-white shadow-[0_28px_90px_rgba(15,23,42,0.12)] lg:grid-cols-[1.04fr_0.96fr] lg:rounded-[30px]">
            <section class="relative hidden overflow-hidden bg-[#0D1B3D] lg:block">
                <img src="{{ asset('images/sigapp-left-panel.png') }}" alt="SIGAPP" class="h-full w-full object-cover">
            </section>

            <section class="bg-[linear-gradient(180deg,_rgba(255,255,255,0.98)_0%,_rgba(250,252,255,0.98)_100%)] px-5 py-8 sm:px-8 sm:py-10 lg:px-12 lg:py-12">
                <div class="mx-auto flex h-full max-w-[450px] flex-col justify-center">
                    <div class="mb-7 flex items-start gap-4 sm:mb-8">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-[20px] border border-blue-100 bg-white shadow-[0_14px_30px_rgba(77,168,255,0.12)]">
                            <img src="{{ asset('images/logo.png') }}" alt="SIGAPP" class="h-full w-full rounded-[20px] object-cover">
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
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Masukkan email SIGAP" class="h-14 w-full rounded-2xl border border-slate-200/90 bg-[linear-gradient(180deg,_#f8fbff_0%,_#edf4ff_100%)] pl-12 pr-4 text-[15px] text-slate-800 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] outline-none transition placeholder:text-slate-400 focus:border-[#4DA8FF] focus:bg-white focus:ring-4 focus:ring-blue-100">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 flex h-6 w-6 -translate-y-1/2 items-center justify-center text-slate-400">
                                    <i class="fa-solid fa-lock text-[15px]"></i>
                                </span>
                                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="h-14 w-full rounded-2xl border border-slate-200/90 bg-[linear-gradient(180deg,_#f8fbff_0%,_#edf4ff_100%)] pl-12 pr-12 text-[15px] text-slate-800 shadow-[inset_0_1px_0_rgba(255,255,255,0.8)] outline-none transition placeholder:text-slate-400 focus:border-[#4DA8FF] focus:bg-white focus:ring-4 focus:ring-blue-100">
                                <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan atau sembunyikan password">
                                    <i id="togglePasswordIcon" class="fa-solid fa-eye text-[15px]"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <label class="flex items-center gap-2.5 cursor-pointer text-slate-600">
                                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-[#0D1B3D] focus:ring-[#4DA8FF]">
                                <span>Ingat saya</span>
                            </label>

                            <a href="{{ route('password.request') }}" class="font-semibold text-[#0D1B3D] transition hover:text-[#4DA8FF]">Lupa password?</a>
                        </div>

                        <div class="rounded-2xl border border-blue-100 bg-[linear-gradient(180deg,_#f7fbff_0%,_#edf4ff_100%)] px-4 py-4 shadow-[0_10px_24px_rgba(77,168,255,0.06)]">
                            <button type="button" id="testNotificationBtn" onclick="window.showLocalNotification()" class="flex items-center gap-2 text-sm font-bold text-[#0D1B3D] transition hover:text-[#4DA8FF]">
                                <i class="fa-solid fa-bell text-base text-[#4DA8FF]"></i>
                                <span>Minta notifikasi</span>
                            </button>
                            <p id="notificationStatus" class="mt-2 text-[13px] leading-5 text-slate-500">Izinkan notifikasi agar kamu menerima update penting.</p>
                        </div>

                        <button type="submit" class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[linear-gradient(180deg,_#0D1B3D_0%,_#081026_100%)] px-4 text-base font-bold text-[#FFFFFF] shadow-[0_16px_35px_rgba(13,27,61,0.3)] transition hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <i class="fa-solid fa-right-to-bracket text-[#4DA8FF]"></i>
                            Masuk
                        </button>
                    </form>

                    <div class="my-6 flex items-center gap-4 text-slate-300 sm:my-7">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span class="text-sm text-slate-400">Akun hanya dibuat oleh team IT.</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <p class="flex flex-wrap items-center justify-center gap-2 text-center text-sm text-slate-500">
                        <i class="fa-solid fa-headset text-slate-400"></i>
                        Butuh bantuan?
                        <a href="https://wa.me/6281334253334" target="_blank" class="font-semibold text-[#0D1B3D] transition hover:text-[#4DA8FF]">Hubungi Admin Sistem</a>
                    </p>
                </div>
            </section>
        </div>
    </div>

    <script src='/app.js'></script>

    <script>
        window.showLocalNotification = async function () {
            const button = document.getElementById('testNotificationBtn');
            const status = document.getElementById('notificationStatus');

            if (!('Notification' in window)) {
                status.textContent = 'Browser kamu belum mendukung notifikasi.';
                return;
            }

            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                status.textContent = 'Notifikasi web hanya bekerja di HTTPS. Pakai domain HTTPS atau localhost.';
                return;
            }

            button.disabled = true;
            button.querySelector('span').textContent = 'Meminta izin...';

            try {
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    status.textContent = 'Izin notifikasi ditolak. Aktifkan dari pengaturan browser.';
                    return;
                }

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
                button.querySelector('span').textContent = 'Minta notifikasi';
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