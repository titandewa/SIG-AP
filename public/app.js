// Cek apakah browser mendukung Web Notifications
if (!("Notification" in window)) {
  alert("Browser ini tidak mendukung Web Notifications.");
} 
// Cek apakah izin sudah diberikan
else if (Notification.permission === "granted") {
  // Jika sudah diizinkan, langsung tampilkan notifikasi
  //munculkanNotifikasi();
} 
    //munculkanNotifikasi();

// Fungsi untuk membuat dan menampilkan notifikasi
function munculkanNotifikasi() {
  const options = {
    body: "Ini adalah isi pesan dari notifikasi web.",
    icon: "https://via.placeholder.com/128", // Opsional: URL gambar/icon
    badge: "https://via.placeholder.com/128" // Opsional: Icon kecil di bar notifikasi
  };

  const notification = new Notification("Judul Notifikasi!", options);

  // Opsional: Aksi ketika notifikasi diklik
  notification.onclick = function() {
    window.focus();
    alert("Notifikasi diklik!");
    notification.close();
  };
}