<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

// Filter tanggal
$dari  = isset($_GET['dari'])  ? $_GET['dari']  : date('Y-m-01');
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Query riwayat
if ($search) {
    $stmt = mysqli_prepare($conn, "
        SELECT t.*, m.nama as nama_member, a.nama as nama_kasir
        FROM transaksi t
        LEFT JOIN member m ON t.member_id = m.id
        LEFT JOIN admin a ON t.kasir_id = a.id
        WHERE (m.nama LIKE ? OR t.kode_transaksi LIKE ?)
        AND DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at DESC
    ");
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $dari, $sampai);
} else {
    $stmt = mysqli_prepare($conn, "
        SELECT t.*, m.nama as nama_member, a.nama as nama_kasir
        FROM transaksi t
        LEFT JOIN member m ON t.member_id = m.id
        LEFT JOIN admin a ON t.kasir_id = a.id
        WHERE DATE(t.created_at) BETWEEN ? AND ?
        ORDER BY t.created_at DESC
    ");
    mysqli_stmt_bind_param($stmt, 'ss', $dari, $sampai);
}
mysqli_stmt_execute($stmt);
$riwayat = mysqli_stmt_get_result($stmt);

// Hitung total periode
$stmt2 = mysqli_prepare($conn, "
    SELECT COUNT(*) as total_trx, SUM(total_bayar) as total_omzet
    FROM transaksi
    WHERE DATE(created_at) BETWEEN ? AND ?
");
mysqli_stmt_bind_param($stmt2, 'ss', $dari, $sampai);
mysqli_stmt_execute($stmt2);
$summary = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="topbar">
            <div class="topbar-title">✦ Riwayat Transaksi</div>
            <div class="topbar-date">🎀 <?= date('d F Y') ?></div>
        </div>

        <!-- Summary periode -->
        <div class="stats-grid" style="margin-bottom:1.5rem;">
            <div class="stat-card">
                <span class="stat-icon">🛍️</span>
                <div class="stat-label">Total Transaksi Periode</div>
                <div class="stat-value">
                    <?= $summary['total_trx'] ?> <span>trx</span>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">💸</span>
                <div class="stat-label">Total Omzet Periode</div>
                <div class="stat-value">
                    Rp <span><?= number_format($summary['total_omzet'] ?? 0, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="section-card">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                <div class="section-title" style="margin-bottom:0; border:none; padding:0;">
                    🎀 Data Transaksi
                </div>
            </div>

            <!-- Filter -->
            <form method="GET" class="filter-bar">
                <input type="text" name="search" class="search-input"
                       placeholder="✦ Cari nama / kode transaksi..."
                       value="<?= htmlspecialchars($search) ?>"
                       style="flex:1; min-width:180px;">
                <input type="date" name="dari"   class="form-control" value="<?= $dari ?>">
                <input type="date" name="sampai" class="form-control" value="<?= $sampai ?>">
                <button type="submit" class="btn btn-pink">Cari</button>
                <a href="riwayat.php" class="btn btn-outline">Reset</a>
                <a href="export_pdf.php?dari=<?= $dari ?>&sampai=<?= $sampai ?>"
   class="btn btn-pink" target="_blank">
    📄 Export PDF
</a>
            </form>

            <!-- Tabel -->
            <table class="pink-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Transaksi</th>
                        <th>Member</th>
                        <th>Total Belanja</th>
                        <th>Diskon</th>
                        <th>Total Bayar</th>
                        <th>Poin</th>
                        <th>Kasir</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($riwayat) > 0):
                        while ($row = mysqli_fetch_assoc($riwayat)):
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <span class="badge badge-pink">
                                <?= $row['kode_transaksi'] ?>
                            </span>
                        </td>
                        <td><?= $row['nama_member'] ?? '<span style="color:rgba(255,255,255,0.3)">Non Member</span>' ?></td>
                        <td>Rp <?= number_format($row['total_belanja'], 0, ',', '.') ?></td>
                        <td class="pink" style="color:#ff69b4;">
                            <?= $row['diskon'] > 0 ? '— Rp ' . number_format($row['diskon'], 0, ',', '.') : '—' ?>
                        </td>
                        <td style="font-weight:700;">
                            Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
                        </td>
                        <td>
                            <span class="badge badge-purple">
                                +<?= $row['poin_dapat'] ?>
                            </span>
                        </td>
                        <td style="color:rgba(255,255,255,0.5); font-size:12px;">
                            <?= htmlspecialchars($row['nama_kasir'] ?? '—') ?>
                        </td>
                        <td style="color:rgba(255,255,255,0.5); font-size:12px;">
                            <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
                        </td>
                        <td>
                            <a href="struk.php?id=<?= $row['id'] ?>"
                               class="btn btn-outline btn-sm">
                                🧾 Struk
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <div class="empty-state-icon">🛍️</div>
                                <div class="empty-state-text">
                                    ✦ Belum ada transaksi di periode ini ✦
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>