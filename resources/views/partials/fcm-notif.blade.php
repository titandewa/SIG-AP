<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="manifest" href="{{ asset('manifest.json') }}">

<button id="btnAktifkanNotif" type="button"
    style="position:fixed;bottom:calc(50vh + 10px);right:16px;z-index:99999;background:#312e81;color:#fff;border:none;padding:10px 14px;border-radius:8px;font-size:13px;">
    Aktifkan Notifikasi
</button>

<script type="module" src="/firebase-notif.js"></script>