// ================== CONFIG ==================
const DEBUG_MODE = false; // set true kalau mau lihat debug box lagi

// ================== DEBUG BOX ==================
function showDebug(msg) {
  if (!DEBUG_MODE) return;
  let box = document.getElementById("fcmDebugBox");
  if (!box) {
    box = document.createElement("div");
    box.id = "fcmDebugBox";
    box.style.cssText = "position:fixed;bottom:0;left:0;right:0;max-height:50vh;overflow-y:auto;background:#000;color:#0f0;font-size:11px;padding:8px;z-index:99999;white-space:pre-wrap;font-family:monospace;";
    document.body.appendChild(box);
  }
  const line = document.createElement("div");
  line.textContent = new Date().toLocaleTimeString() + " - " + msg;
  box.appendChild(line);
}

// ================== LOADING OVERLAY ==================
function showLoadingOverlay() {
  const overlay = document.createElement("div");
  overlay.id = "fcmLoadingOverlay";
  overlay.style.cssText = `
    position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.5); z-index:99998;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
  `;
  overlay.innerHTML = `
    <div style="border:4px solid #f3f3f3; border-top:4px solid #3b82f6; border-radius:50%; width:40px; height:40px; animation:fcmSpin 1s linear infinite;"></div>
    <div style="color:#fff; margin-top:12px; font-size:14px;">Tunggu sebentar...</div>
    <style>@keyframes fcmSpin { 0% { transform:rotate(0deg); } 100% { transform:rotate(360deg); } }</style>
  `;
  document.body.appendChild(overlay);
}

function hideLoadingOverlay() {
  const overlay = document.getElementById("fcmLoadingOverlay");
  if (overlay) overlay.remove();
}

// ================== RESULT MODAL ==================
function showResultModal(success, msg) {
  const modal = document.createElement("div");
  modal.style.cssText = `
    position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.5); z-index:99999;
    display:flex; align-items:center; justify-content:center;
  `;
  modal.innerHTML = `
    <div style="background:#fff; border-radius:12px; padding:24px 20px; width:280px; text-align:center; box-shadow:0 8px 24px rgba(0,0,0,0.2);">
      <div style="font-size:16px; font-weight:600; color:${success ? "#16a34a" : "#dc2626"}; margin-bottom:8px;">
        ${success ? "Berhasil" : "Gagal"}
      </div>
      <div style="font-size:14px; color:#374151; margin-bottom:16px;">${msg}</div>
      <button id="fcmModalOkBtn" style="background:#3b82f6; color:#fff; border:none; padding:8px 24px; border-radius:6px; font-size:14px; cursor:pointer;">Okey</button>
    </div>
  `;
  document.body.appendChild(modal);

  document.getElementById("fcmModalOkBtn").addEventListener("click", () => {
    if (success) {
      window.location.reload();
    } else {
      modal.remove();
    }
  });
}

// ================== FIREBASE INIT ==================
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging.js";

const firebaseConfig = {
  apiKey: "AIzaSyDXk4pegvTKWiNPFaXKtZoxdvyAxCeIdD0",
  authDomain: "sigap-notif-84bb6.firebaseapp.com",
  projectId: "sigap-notif-84bb6",
  storageBucket: "sigap-notif-84bb6.firebasestorage.app",
  messagingSenderId: "595314558998",
  appId: "1:595314558998:web:9dbbaeac5af18c7bea4d37",
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

const VAPID_KEY = "BNpe0M-28BH0N1vupdLEKwomJeeTW5HJKwXrN-iEqULLEzMdGnTUX-vmwITtp4ae2iE_6vDXnPKcEGYqiCnbzBQ";

// ================== MAIN FUNCTION ==================
async function requestPermissionAndGetToken(showPopup = false) {
  try {
    showDebug("Mulai proses...");

    if (!("Notification" in window)) {
      showDebug("Browser tidak mendukung Notification API");
      if (showPopup) {
        hideLoadingOverlay();
        showResultModal(false, "Browser tidak mendukung notifikasi");
      }
      return;
    }
    showDebug("Notification API tersedia");

    const permission = await Notification.requestPermission();
    showDebug("Permission: " + permission);
    if (permission !== "granted") {
      showDebug("Izin ditolak, stop.");
      if (showPopup) {
        hideLoadingOverlay();
        showResultModal(false, "Izin notifikasi ditolak");
      }
      return;
    }

    showDebug("Registrasi service worker...");
    const registration = await navigator.serviceWorker.register("/firebase-messaging-sw.js");
    showDebug("SW registered: " + registration.scope);

    await navigator.serviceWorker.ready;
    showDebug("SW ready");

    showDebug("Meminta token FCM...");
    const token = await getToken(messaging, {
      vapidKey: VAPID_KEY,
      serviceWorkerRegistration: registration,
    });

    if (token) {
      showDebug("Token didapat: " + token.substring(0, 20) + "...");
      const res = await fetch("/fcm/save-token", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
        body: JSON.stringify({ token }),
      });
      showDebug("Save token status: " + res.status);
      if (showPopup) {
        hideLoadingOverlay();
        showResultModal(res.ok, res.ok ? "Notifikasi berhasil diaktifkan" : "Gagal menyimpan token notifikasi");
      }
    } else {
      showDebug("Gagal dapat token (null).");
      if (showPopup) {
        hideLoadingOverlay();
        showResultModal(false, "Gagal mendapatkan token notifikasi");
      }
    }
  } catch (err) {
    showDebug("ERROR: " + err.name + " - " + err.message);
    if (showPopup) {
      hideLoadingOverlay();
      showResultModal(false, "Terjadi kesalahan saat mengaktifkan notifikasi");
    }
    console.error("Error saat setup FCM:", err);
  }
}

// ================== TOMBOL & AUTO-RUN ==================
const btn = document.getElementById("btnAktifkanNotif");

if (Notification.permission === "granted") {
  requestPermissionAndGetToken(false); // auto-run, tidak munculkan popup
  if (btn) btn.style.display = "none";
} else if (btn) {
  btn.addEventListener("click", () => {
    showLoadingOverlay();
    requestPermissionAndGetToken(true);
  });
}

// ================== FOREGROUND MESSAGE HANDLER ==================
onMessage(messaging, (payload) => {
  console.log("Pesan diterima (foreground):", payload);
  const data = payload.data || {};

  const notif = new Notification(data.title || "SIGAP", {
    body: data.body || "",
    icon: "/favicon.ico",
    badge: "/favicon.ico",
    data: data,
  });

  notif.onclick = () => {
    const url = data.url || "/";
    window.focus();
    window.location.href = url;
  };
});