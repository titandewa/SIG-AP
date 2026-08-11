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
    // Cek apakah browser mendukung Notification API
    if (!("Notification" in window)) {
      console.log("Browser ini tidak mendukung notifikasi.");
      return;
    }

    const permission = await Notification.requestPermission();
    if (permission !== "granted") {
      console.log("Izin notifikasi ditolak.");
      return;
    }

    const registration = await navigator.serviceWorker.register("/firebase-messaging-sw.js");
    await navigator.serviceWorker.ready;

    const token = await getToken(messaging, {
      vapidKey: VAPID_KEY,
      serviceWorkerRegistration: registration,
    });

    if (token) {
      console.log("FCM Token:", token);
      await fetch("/fcm/save-token", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
        body: JSON.stringify({ token }),
      });
    } else {
      console.log("Gagal mendapatkan token.");
    }
  } catch (err) {
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