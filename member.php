<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

$success = '';
$error   = '';

// ===== TAMBAH MEMBER =====
if (isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $rfid  = trim($_POST['rfid_id']);
    $nama  = trim($_POST['nama']);
    $hp    = trim($_POST['no_hp']);
    $email = trim($_POST['email']);

    // Cek RFID sudah ada belum
    $cek  = mysqli_prepare($conn, "SELECT id FROM member WHERE rfid_id = ?");
    mysqli_stmt_bind_param($cek, 's', $rfid);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        $error = 'RFID ID sudah terdaftar!';
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO member (rfid_id, nama, no_hp, email) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $rfid, $nama, $hp, $email);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Member berhasil ditambahkan! 🎀';
        }
    }
}

// ===== HAPUS MEMBER =====
if (isset($_GET['hapus'])) {
    $id   = (int) $_GET['hapus'];
    $stmt = mysqli_prepare($conn, "DELETE FROM member WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $success = 'Member berhasil dihapus!';
}

// ===== EDIT MEMBER =====
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id    = (int) $_POST['id'];
    $nama  = trim($_POST['nama']);
    $hp    = trim($_POST['no_hp']);
    $email = trim($_POST['email']);

    $stmt = mysqli_prepare($conn, "UPDATE member SET nama=?, no_hp=?, email=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $hp, $email, $id);
    if (mysqli_stmt_execute($stmt)) {
        $success = 'Data member berhasil diupdate! 🎀';
    }
}

// ===== AMBIL DATA MEMBER =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM member WHERE nama LIKE ? OR rfid_id LIKE ? OR no_hp LIKE ? ORDER BY created_at DESC");
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $members = mysqli_stmt_get_result($stmt);
} else {
    $members = mysqli_query($conn, "SELECT * FROM member ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="topbar">
            <div class="topbar-title">✦ Manajemen Member</div>
            <div class="topbar-date">🎀 <?= date('d F Y') ?></div>
        </div>

        <?php if ($success): ?>
            <div class="alert-success">✦ <?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-error">✦ <?= $error ?></div>
        <?php endif; ?>

        <div class="section-card">

            <!-- Search + Tombol Tambah -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div class="section-title" style="margin-bottom:0; border:none; padding:0;">
                    🎀 Data Member
                </div>
                <button class="btn btn-pink" onclick="bukaModal('modalTambah')">
                    ✦ Tambah Member
                </button>
            </div>

            <form method="GET" class="search-bar">
                <input type="text" name="search" class="search-input"
                       placeholder="✦ Cari nama, RFID, atau no HP..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline">Cari</button>
                <?php if ($search): ?>
                    <a href="member.php" class="btn btn-outline">Reset</a>
                <?php endif; ?>
            </form>

            <table class="pink-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>RFID ID</th>
                        <th>Nama Member</th>
                        <th>No HP</th>
                        <th>Total Poin</th>
                        <th>Total Belanja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($members) > 0):
                        while ($m = mysqli_fetch_assoc($members)):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="badge badge-pink"><?= $m['rfid_id'] ?></span></td>
                        <td><?= htmlspecialchars($m['nama']) ?></td>
                        <td><?= $m['no_hp'] ?: '—' ?></td>
                        <td><span class="badge badge-purple">⭐ <?= $m['total_poin'] ?></span></td>
                        <td>Rp <?= number_format($m['total_belanja'], 0, ',', '.') ?></td>
                        <td style="display:flex; gap:6px;">
                            <button class="btn btn-outline btn-sm"
                                onclick="bukaEdit(<?= $m['id'] ?>, '<?= addslashes($m['nama']) ?>', '<?= $m['no_hp'] ?>', '<?= $m['email'] ?>')">
                                Edit
                            </button>
                            <a href="?hapus=<?= $m['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus member ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:rgba(255,105,180,0.4); padding:2rem;">
                            ✦ Belum ada data member ✦
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH MEMBER -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <button class="modal-close" onclick="tutupModal('modalTambah')">✕</button>
        <div class="modal-title">✦ Tambah Member Baru</div>

        <form method="POST">
            <input type="hidden" name="action" value="tambah">

            <div class="rfid-box">
                <div class="rfid-label">✦ Simulasi RFID Card ID</div>
                <input type="text" name="rfid_id" id="rfid_input" class="form-control"
                       placeholder="Ketik atau generate ID kartu..." required
                       style="text-align:center; letter-spacing:2px;">
                <button type="button" class="rfid-generate" onclick="generateRFID()">
                    🎴 Generate ID Otomatis
                </button>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Member</label>
                <input type="text" name="nama" class="form-control"
                       placeholder="Nama lengkap..." required>
            </div>
            <div class="form-group">
                <label class="form-label">No HP</label>
                <input type="text" name="no_hp" class="form-control"
                       placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       placeholder="email@contoh.com">
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
                <button type="submit" class="btn btn-pink" style="flex:1;">✦ Simpan Member</button>
                <button type="button" class="btn btn-outline" onclick="tutupModal('modalTambah')">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT MEMBER -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <button class="modal-close" onclick="tutupModal('modalEdit')">✕</button>
        <div class="modal-title">✦ Edit Data Member</div>

        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div class="form-group">
                <label class="form-label">Nama Member</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">No HP</label>
                <input type="text" name="no_hp" id="edit_hp" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-control">
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
                <button type="submit" class="btn btn-pink" style="flex:1;">✦ Update Member</button>
                <button type="button" class="btn btn-outline" onclick="tutupModal('modalEdit')">Batal</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>