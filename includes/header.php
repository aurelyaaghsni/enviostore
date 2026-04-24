<?php
// Cek session — kalau belum login, balik ke login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
?>