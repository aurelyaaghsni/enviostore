<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

// Ambil data statistik
$total_member     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM member"))['total'];
$total_transaksi  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi"))['total'];
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_bayar) as total FROM transaksi"))['total'] ?? 0;
$transaksi_hari   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM transaksi WHERE DATE(created_at) = CURDATE()"))['total'];

// Ambil 5 transaksi terakhir
$recent = mysqli_query($conn, "
    SELECT t.kode_transaksi, m.nama, t.total_bayar, t.poin_dapat, t.created_at
    FROM transaksi t
    LEFT JOIN member m ON t.member_id = m.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-title">✦ Dashboard</div>
            <div class="topbar-date">
                🎀 <?= date('l, d F Y') ?>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-icon">🪪</span>
                <div class="stat-label">Total Member</div>
                <div class="stat-value"><?= $total_member ?> <span>member</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🛍️</span>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value"><?= $total_transaksi ?> <span>trx</span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">💸</span>
                <div class="stat-label">Total Pendapatan</div>
                <div class="stat-value">Rp <span><?= number_format($total_pendapatan, 0, ',', '.') ?></span></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📅</span>
                <div class="stat-label">Transaksi Hari Ini</div>
                <div class="stat-value"><?= $transaksi_hari ?> <span>trx</span></div>
            </div>
        </div>

        <!-- Transaksi Terakhir -->
        <div class="section-card">
            <div class="section-title">🎀 Transaksi Terakhir</div>
            <table class="pink-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Member</th>
                        <th>Total Bayar</th>
                        <th>Poin</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                        <tr>
                            <td><span class="badge badge-pink"><?= $row['kode_transaksi'] ?></span></td>
                            <td><?= $row['nama'] ?? '— Non Member —' ?></td>
                            <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                            <td><span class="badge badge-purple">+<?= $row['poin_dapat'] ?> poin</span></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:rgba(255,105,180,0.4); padding: 2rem;">
                                ✦ Belum ada transaksi ✦
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