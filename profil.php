<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

$success = '';
$error   = '';

// ===== GANTI PASSWORD =====
if (isset($_POST['action']) && $_POST['action'] === 'ganti_password') {
    $password_lama = md5(trim($_POST['password_lama']));
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi    = trim($_POST['konfirmasi']);

    // Cek password lama bener ga
    $stmt = mysqli_prepare($conn, "SELECT id FROM admin WHERE id = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, 'is', $_SESSION['user'], $password_lama);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) === 0) {
        $error = 'Password lama salah!';
    } elseif ($password_baru !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok!';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } else {
        $upd = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
        $hash = md5($password_baru);
        mysqli_stmt_bind_param($upd, 'si', $hash, $_SESSION['user']);
        mysqli_stmt_execute($upd);
        $success = 'Password berhasil diubah! 🎀';
    }
}

// ===== TAMBAH USER (admin only) =====
if (isset($_POST['action']) && $_POST['action'] === 'tambah_user') {
    adminOnly();
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password']));
    $role     = $_POST['role'];

    // Cek username sudah ada
    $cek = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($cek, 's', $username);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        $error = 'Username sudah dipakai!';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO admin (nama, username, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $username, $password, $role);
        mysqli_stmt_execute($stmt);
        $success = 'User baru berhasil ditambahkan! 🎀';
    }
}

// ===== HAPUS USER (admin only) =====
if (isset($_GET['hapus_user'])) {
    adminOnly();
    $id = (int) $_GET['hapus_user'];
    if ($id === $_SESSION['user']) {
        $error = 'Tidak bisa hapus akun sendiri!';
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM admin WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $success = 'User berhasil dihapus!';
    }
}

// Ambil data user login
$me = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admin WHERE id = {$_SESSION['user']}"));

// Ambil semua user (admin only)
$all_users = null;
if ($_SESSION['role'] === 'admin') {
    $all_users = mysqli_query($conn, "SELECT * FROM admin ORDER BY role, nama");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="topbar">
            <div class="topbar-title">✦ Profil & Pengaturan</div>
            <div class="topbar-date">🎀 <?= date('d F Y') ?></div>
        </div>

        <?php if ($success): ?>
            <div class="alert-success">✦ <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-error">✦ <?= $error ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

            <!-- Kartu Profil -->
            <div class="section-card">
                <div class="section-title">🎀 Profil Saya</div>

                <div style="display:flex; align-items:center; gap:16px; margin-bottom:1.5rem;">
                    <div class="user-avatar" style="width:56px; height:56px; font-size:22px;">
                        <?= strtoupper(substr($me['nama'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size:16px; font-weight:700; color:#fff;">
                            <?= htmlspecialchars($me['nama']) ?>
                        </div>
                        <div style="font-size:11px; color:rgba(255,105,180,0.6); letter-spacing:1px; margin-top:3px;">
                            ✦ <?= ucfirst($me['role']) ?>
                        </div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; padding:8px 0;
                                border-bottom:1px solid rgba(255,105,180,0.08); font-size:13px;">
                        <span style="color:rgba(255,255,255,0.45);">Username</span>
                        <span class="badge badge-pink"><?= $me['username'] ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0;
                                border-bottom:1px solid rgba(255,105,180,0.08); font-size:13px;">
                        <span style="color:rgba(255,255,255,0.45);">Role</span>
                        <span class="badge badge-purple"><?= ucfirst($me['role']) ?></span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13px;">
                        <span style="color:rgba(255,255,255,0.45);">Bergabung</span>
                        <span style="color:#fff;"><?= date('d F Y', strtotime($me['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Ganti Password -->
            <div class="section-card">
                <div class="section-title">🔐 Ganti Password</div>

                <form method="POST">
                    <input type="hidden" name="action" value="ganti_password">

                    <div class="form-group">
                        <label class="form-label">Password Lama</label>
                        <input type="password" name="password_lama" class="form-control"
                               placeholder="Masukkan password lama..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control"
                               placeholder="Minimal 6 karakter..." required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="konfirmasi" class="form-control"
                               placeholder="Ulangi password baru..." required>
                    </div>

                    <button type="submit" class="btn btn-pink" style="width:100%; margin-top:0.5rem;">
                        ✦ Simpan Password Baru
                    </button>
                </form>
            </div>
        </div>

        <!-- Manajemen User — khusus admin -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <div class="section-card" style="margin-top:1.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div class="section-title" style="margin-bottom:0; border:none; padding:0;">
                    🎀 Manajemen User
                </div>
                <button class="btn btn-pink" onclick="bukaModal('modalTambahUser')">
                    ✦ Tambah User
                </button>
            </div>

            <table class="pink-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($u = mysqli_fetch_assoc($all_users)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($u['nama']) ?></td>
                        <td><span class="badge badge-pink"><?= $u['username'] ?></span></td>
                        <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-purple' : 'badge-pink' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span></td>
                        <td style="font-size:12px; color:rgba(255,255,255,0.5);">
                            <?= date('d/m/Y', strtotime($u['created_at'])) ?>
                        </td>
                        <td>
                            <?php if ($u['id'] !== $_SESSION['user']): ?>
                            <a href="?hapus_user=<?= $u['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus user ini?')">
                                Hapus
                            </a>
                            <?php else: ?>
                            <span style="font-size:11px; color:rgba(255,105,180,0.4);">— kamu —</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal-overlay" id="modalTambahUser">
    <div class="modal-box">
        <button class="modal-close" onclick="tutupModal('modalTambahUser')">✕</button>
        <div class="modal-title">✦ Tambah User Baru</div>

        <form method="POST">
            <input type="hidden" name="action" value="tambah_user">

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control"
                       placeholder="Nama lengkap..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="Username untuk login..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Password..." required>
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" style="color:#ff69b4;">
                    <option value="kasir">Kasir</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
                <button type="submit" class="btn btn-pink" style="flex:1;">✦ Simpan User</button>
                <button type="button" class="btn btn-outline"
                        onclick="tutupModal('modalTambahUser')">Batal</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>