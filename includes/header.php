<?php
// Cek session — kalau belum login, balik ke login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Fungsi cek role — dipanggil di halaman khusus admin
function adminOnly() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: dashboard.php?error=akses_ditolak');
        exit();
    }
}
?>