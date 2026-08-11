function showDebug(msg) {
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

async function requestPermissionAndGetToken() {
  try {
    showDebug("Mulai proses...");

    if (!("Notification" in window)) {
      showDebug("Browser tidak mendukung Notification API");
      return;
    }
    showDebug("Notification API tersedia");

    const permission = await Notification.requestPermission();
    showDebug("Permission: " + permission);
    if (permission !== "granted") {
      showDebug("Izin ditolak, stop.");
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
    } else {
      showDebug("Gagal dapat token (null).");
    }
  } catch (err) {
    showDebug("ERROR: " + err.name + " - " + err.message);
    console.error("Error saat setup FCM:", err);
  }
}

requestPermissionAndGetToken();

// Handler untuk pesan yang diterima saat app di foreground
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