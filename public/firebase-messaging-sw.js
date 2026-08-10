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

messaging.onBackgroundMessage((payload) => {
  const data = payload.data || {};
  const notificationTitle = data.title || "SIGAP";
  const notificationOptions = {
    body: data.body || "",
    icon: "/favicon.ico",
    data: data,
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  const url = (event.notification.data && event.notification.data.url) || "/app/aspri/dashboard";

  event.waitUntil(clients.openWindow(url));
});