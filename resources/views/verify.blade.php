<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAP - Verifikasi Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans text-slate-800 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg border border-slate-200 p-8 text-center">

        @if($document && $document->status === 'DISETUJUI')
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-shield-check text-3xl text-emerald-600"></i>
            </div>
            <h1 class="text-lg font-bold text-emerald-700 mb-1">Dokumen Sah & Terverifikasi</h1>
            <p class="text-xs text-slate-500 mb-6">Dokumen ini tercatat resmi di sistem SIGAP_V1</p>

            <div class="text-left bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Judul</span>
                    <span class="font-semibold text-right">{{ $document->title }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Nomor</span>
                    <span class="font-semibold">{{ $document->document_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Diajukan oleh</span>
                    <span class="font-semibold">{{ $document->user->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Ditandatangani</span>
                    <span class="font-semibold">{{ $document->signed_at?->translatedFormat('d F Y, H:i') ?? '-' }}</span>
                </div>
            </div>
        @else
            <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-xmark text-3xl text-rose-600"></i>
            </div>
            <h1 class="text-lg font-bold text-rose-700 mb-1">Dokumen Tidak Ditemukan</h1>
            <p class="text-xs text-slate-500">Kode verifikasi tidak valid atau dokumen belum disahkan.</p>
        @endif

        <p class="text-[10px] text-slate-400 mt-6">SIGAP_V1 — Sistem Pengajuan & Approval SK</p>
    </div>

</body>
</html>