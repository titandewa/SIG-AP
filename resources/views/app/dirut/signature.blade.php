<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Atur Tanda Tangan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    <!-- Topbar -->
    <nav class="bg-indigo-950 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('app.dirut.dashboard') }}" class="bg-indigo-900 hover:bg-indigo-800 p-2 rounded-lg text-xs font-semibold text-indigo-200 flex items-center gap-1 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h1 class="font-bold text-lg leading-none border-l border-indigo-800 pl-3">
                SIGAP_V1 <span class="text-xs text-amber-400 font-normal">/ Atur Tanda Tangan</span>
            </h1>
        </div>
    </nav>

    <div class="max-w-xl mx-auto p-6">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm px-4 py-3 rounded-lg mb-4">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-bold text-slate-900 mb-1">Tanda Tangan Digital Anda</h2>
            <p class="text-xs text-slate-500 mb-4">Tanda tangan ini akan otomatis dipakai setiap kali Anda mengesahkan dokumen SK. Cukup diatur sekali.</p>

            @if(auth()->user()->signature_path)
                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-600 mb-2">Tanda Tangan Tersimpan Saat Ini:</p>
                    <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 flex justify-center">
                        <img src="{{ asset('storage/' . auth()->user()->signature_path) }}" alt="Tanda Tangan" class="h-20">
                    </div>
                </div>
            @endif

            <p class="text-xs font-semibold text-slate-600 mb-2">Gambar Tanda Tangan Baru:</p>
            <canvas id="signatureCanvas" class="border-2 border-dashed border-slate-300 rounded-xl w-full bg-white cursor-crosshair" height="200"></canvas>

            <div class="flex items-center gap-2 mt-3">
                <button type="button" onclick="clearCanvas()" class="text-xs font-semibold text-rose-600 bg-rose-50 border border-rose-200 px-3 py-2 rounded-lg hover:bg-rose-100 transition">
                    <i class="fa-solid fa-eraser mr-1"></i> Hapus
                </button>
                <span class="text-[11px] text-slate-400">Gambar pakai mouse atau jari (di HP/tablet)</span>
            </div>

            <form id="signatureForm" action="{{ route('dirut.signature.save') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="signature_image" id="signatureImageInput">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-md">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Tanda Tangan
                </button>
            </form>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        function resizeCanvas() {
            const ratio = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#1e293b';
        }
        resizeCanvas();

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches.length > 0) {
                return { x: e.touches[0].clientX - rect.left, y: e.touches[0].clientY - rect.top };
            }
            return { x: e.clientX - rect.left, y: e.clientY - rect.top };
        }

        function startDraw(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDraw() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);
        canvas.addEventListener('touchstart', startDraw);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDraw);

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        document.getElementById('signatureForm').addEventListener('submit', function(e) {
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            if (canvas.toDataURL() === blank.toDataURL()) {
                e.preventDefault();
                alert('Silakan gambar tanda tangan terlebih dahulu.');
                return;
            }
            document.getElementById('signatureImageInput').value = canvas.toDataURL('image/png');
        });
    </script>

</body>
</html>