<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP_V1 - Masuk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        <!-- Logo & Judul -->
        <div class="flex flex-col items-center mb-6">
            <div class="bg-indigo-600 text-white w-14 h-14 rounded-2xl flex items-center justify-center font-bold text-2xl shadow-lg shadow-indigo-200 mb-3">
                S
            </div>
            <h1 class="font-bold text-xl text-slate-900">SIGAP_V1</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem Pengajuan &amp; Approval SK</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <h2 class="font-bold text-slate-900 text-lg mb-1">Masuk ke Akun</h2>
            <p class="text-xs text-slate-500 mb-6">Masukkan email dan password kamu untuk melanjutkan.</p>

            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs px-4 py-3 rounded-lg mb-4">
                    <i class="fa-solid fa-circle-check mr-1"></i> {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-300 text-rose-800 text-xs px-4 py-3 rounded-lg mb-4">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="nama@perusahaan.com"
                            class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-9 pr-10 py-2.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i id="togglePasswordIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs text-slate-600">Ingat saya di perangkat ini</span>
                </label>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-md shadow-indigo-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-[11px] text-slate-400 mt-6">
            Akun hanya dibuat oleh admin sistem. Hubungi admin kamu bila ada kendala akses.
        </p>
    </div>

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