importScripts("https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js");

firebase.initializeApp({
  apiKey: "AIzaSyDXk4pegvTKWiNPFaXKtZoxdvyAxCeIdD0",
  authDomain: "sigap-notif-84bb6.firebaseapp.com",
  projectId: "sigap-notif-84bb6",
  storageBucket: "sigap-notif-84bb6.firebasestorage.app",
  messagingSenderId: "595314558998",
  appId: "1:595314558998:web:9dbbaeac5af18c7bea4d37",
});

const messaging = firebase.messaging();

function updateBadgeFromData(data) {
  if (data.badge_count === undefined) return;
  const count = parseInt(data.badge_count, 10) || 0;
  if ('setAppBadge' in navigator) {
    if (count > 0) {
      navigator.setAppBadge(count).catch(() => {});
    } else {
      navigator.clearAppBadge().catch(() => {});
    }
  }
}

messaging.onBackgroundMessage((payload) => {
  const data = payload.data || {};
  updateBadgeFromData(data);
  const notificationTitle = data.title || "SIGAP";
  const notificationOptions = {
    body: data.body || "",
    icon: "/favicon.ico",
    badge: "/favicon.ico",
    data: data,
  };
  self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener("push", function (event) {
  if (!event.data) return;

  let payload = {};
  try {
    payload = event.data.json();
  } catch (e) {
    payload = { title: "SIGAP", body: event.data.text() };
  }

  const data = payload.data || payload;
  updateBadgeFromData(data);
  const notificationTitle = data.title || "SIGAP";
  const notificationOptions = {
    body: data.body || "",
    icon: "/favicon.ico",
    badge: "/favicon.ico",
    data: data,
  };

  event.waitUntil(self.registration.showNotification(notificationTitle, notificationOptions));
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  const data = event.notification.data || {};
  const url = data.url || "/app/aspri/dashboard";

  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url === url && "focus" in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});