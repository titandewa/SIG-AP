<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1e293b;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            text-transform: uppercase;
            margin: 0;
        }
        .header p {
            font-size: 10px;
            color: #64748b;
            margin: 4px 0 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .info-table td {
            padding: 8px;
            font-size: 11px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table td:first-child {
            color: #64748b;
            width: 35%;
        }
        .info-table td:last-child {
            font-weight: bold;
        }
        .status-box {
            text-align: center;
            background: #ecfdf5;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .status-box p:first-child {
            color: #047857;
            font-weight: bold;
            font-size: 14px;
            margin: 0;
        }
        .signature-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
        }
        .signature-section p.label {
            font-size: 10px;
            color: #64748b;
            margin: 0 0 5px;
        }
        .signature-section img {
            height: 60px;
            margin: 5px 0;
        }
        .signature-section p.name {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            border-top: 1px solid #94a3b8;
            display: inline-block;
            padding-top: 4px;
            margin-top: 4px;
        }
        .qr-section {
            text-align: center;
            margin-top: 20px;
        }
        .qr-section img {
            width: 110px;
            height: 110px;
        }
        .qr-section p {
            font-size: 9px;
            color: #64748b;
            margin: 8px 0 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sertifikat Pengesahan Dokumen</h1>
        <p>SIGAP_V1 — Sistem Pengajuan & Approval SK</p>
    </div>

    <div class="status-box">
        <p>✓ DOKUMEN SAH & TERVERIFIKASI</p>
    </div>

    <table class="info-table">
        <tr>
            <td>Judul Dokumen</td>
            <td>{{ $document->title }}</td>
        </tr>
        <tr>
            <td>Nomor Dokumen</td>
            <td>{{ $document->document_number }}</td>
        </tr>
        <tr>
            <td>Diajukan oleh</td>
            <td>{{ $document->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Disahkan</td>
            <td>{{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
    </table>

    <div class="signature-section">
        <p class="label">Ditandatangani secara digital oleh:</p>
        <img src="data:image/png;base64,{{ $signatureBase64 }}" alt="Tanda Tangan">
        <br>
        <p class="name">{{ $dirutName }}<br><span style="font-weight: normal; font-size: 10px;">Direktur Utama</span></p>
    </div>

    <div class="qr-section">
        <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Verifikasi">
        <p>Pindai kode QR ini untuk memverifikasi keaslian dokumen secara online</p>
    </div>

    <div class="footer">
        Sertifikat ini digenerate otomatis oleh sistem SIGAP_V1 dan sah tanpa memerlukan tanda tangan basah.
    </div>

</body>
</html>