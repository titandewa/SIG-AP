<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Verifikasi Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800 min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg border border-slate-200 p-5 sm:p-8 text-center">

        @if($document && $document->status === 'DISETUJUI')
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-check text-2xl sm:text-3xl text-emerald-600"></i>
            </div>
            <h1 class="text-base sm:text-lg font-bold text-emerald-700 mb-1">Dokumen Sah & Terverifikasi</h1>
            <p class="text-xs text-slate-500 mb-6">Dokumen ini tercatat resmi di sistem SIGAP_V1</p>

            <div class="text-left bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3 text-sm">
                <div>
                    <p class="text-xs text-slate-500">Judul</p>
                    <p class="font-semibold break-words">{{ $document->title }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Nomor</p>
                    <p class="font-semibold break-words">{{ $document->document_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Diajukan oleh</p>
                    <p class="font-semibold break-words">{{ $document->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Ditandatangani</p>
                    <p class="font-semibold break-words">{{ $document->signed_at?->translatedFormat('d F Y, H:i') ?? '-' }}</p>
                </div>
            </div>
        @else
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-xmark text-2xl sm:text-3xl text-rose-600"></i>
            </div>
            <h1 class="text-base sm:text-lg font-bold text-rose-700 mb-1">Dokumen Tidak Ditemukan</h1>
            <p class="text-xs text-slate-500">Kode verifikasi tidak valid atau dokumen belum disahkan.</p>
        @endif

        <p class="text-[10px] text-slate-400 mt-6">SIGAP_V1 — Sistem Pengajuan & Approval SK</p>
    </div>

</body>
</html>