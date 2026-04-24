<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

// Ambil ID transaksi dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: transaksi.php');
    exit();
}

// Ambil data transaksi + member + kasir
$stmt = mysqli_prepare($conn, "
    SELECT t.*, m.nama as nama_member, m.no_hp, m.rfid_id,
           m.total_poin as poin_sekarang,
           a.nama as nama_kasir
    FROM transaksi t
    LEFT JOIN member m ON t.member_id = m.id
    LEFT JOIN admin a ON t.kasir_id = a.id
    WHERE t.id = ?
");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$trx = mysqli_fetch_assoc($result);

if (!$trx) {
    header('Location: transaksi.php');
    exit();
}

// Hitung diskon persen (buat tampilan)
$diskon_persen = $trx['total_belanja'] > 0
    ? round(($trx['diskon'] / $trx['total_belanja']) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="topbar">
            <div class="topbar-title">✦ Struk Transaksi</div>
            <div class="topbar-date">🎀 <?= date('d F Y') ?></div>
        </div>

        <div class="struk-wrapper">

            <!-- Struk Card -->
            <div class="struk-card" id="strukturCetak">

                <!-- Header -->
                <div class="struk-header">
                    <div class="struk-logo">
                        <svg width="52" height="52" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <radialGradient id="bgG2" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#2a0040"/>
                                    <stop offset="100%" stop-color="#0f0018"/>
                                </radialGradient>
                                <linearGradient id="pG2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#ff69b4"/>
                                    <stop offset="100%" stop-color="#ff1493"/>
                                </linearGradient>
                            </defs>
                            <circle cx="36" cy="36" r="35" fill="url(#bgG2)" stroke="#ff149366" stroke-width="1"/>
                            <ellipse cx="36" cy="36" rx="16" ry="10" fill="none" stroke="url(#pG2)" stroke-width="1.5"/>
                            <ellipse cx="36" cy="36" rx="10" ry="16" fill="none" stroke="url(#pG2)" stroke-width="1.5"/>
                            <circle cx="36" cy="36" r="5" fill="url(#pG2)"/>
                            <circle cx="36" cy="20" r="2.5" fill="#ffb6d9"/>
                            <circle cx="36" cy="52" r="2.5" fill="#ffb6d9"/>
                            <circle cx="20" cy="36" r="2.5" fill="#ffb6d9"/>
                            <circle cx="52" cy="36" r="2.5" fill="#ffb6d9"/>
                        </svg>
                    </div>
                    <span class="struk-store-name">Envio Store</span>
                    <div class="struk-sub">🎀 Member Loyalty System 🎀</div>
                    <div class="struk-kode"><?= $trx['kode_transaksi'] ?></div>
                </div>

                <!-- Info Transaksi -->
                <div class="struk-section">
                    <div class="struk-section-title">✦ Info Transaksi</div>
                    <div class="struk-row">
                        <span class="struk-row-label">Tanggal</span>
                        <span class="struk-row-val"><?= date('d/m/Y', strtotime($trx['created_at'])) ?></span>
                    </div>
                    <div class="struk-row">
                        <span class="struk-row-label">Jam</span>
                        <span class="struk-row-val"><?= date('H:i:s', strtotime($trx['created_at'])) ?></span>
                    </div>
                    <div class="struk-row">
                        <span class="struk-row-label">Kasir</span>
                        <span class="struk-row-val"><?= htmlspecialchars($trx['nama_kasir'] ?? '—') ?></span>
                    </div>
                </div>

                <!-- Info Member -->
                <div class="struk-section">
                    <div class="struk-section-title">✦ Data Member</div>
                    <?php if ($trx['nama_member']): ?>
                        <div class="struk-row">
                            <span class="struk-row-label">Nama</span>
                            <span class="struk-row-val"><?= htmlspecialchars($trx['nama_member']) ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-row-label">No HP</span>
                            <span class="struk-row-val"><?= $trx['no_hp'] ?: '—' ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-row-label">RFID Card</span>
                            <span class="struk-row-val pink"><?= $trx['rfid_id'] ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-row-label">Total Poin Sekarang</span>
                            <span class="struk-row-val pink">⭐ <?= $trx['poin_sekarang'] ?> poin</span>
                        </div>
                    <?php else: ?>
                        <div class="struk-row">
                            <span class="struk-row-label">Status</span>
                            <span class="struk-row-val" style="color:rgba(255,255,255,0.4);">— Non Member —</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Rincian Belanja -->
                <div class="struk-section">
                    <div class="struk-section-title">✦ Rincian Belanja</div>
                    <div class="struk-row">
                        <span class="struk-row-label">Total Belanja</span>
                        <span class="struk-row-val">
                            Rp <?= number_format($trx['total_belanja'], 0, ',', '.') ?>
                        </span>
                    </div>
                    <div class="struk-row">
                        <span class="struk-row-label">
                            Diskon <?= $diskon_persen > 0 ? "($diskon_persen%)" : '' ?>
                        </span>
                        <span class="struk-row-val pink">
                            — Rp <?= number_format($trx['diskon'], 0, ',', '.') ?>
                        </span>
                    </div>
                </div>

                <!-- Total Bayar -->
                <div class="struk-total">
                    <div>
                        <div class="struk-total-label">Total Bayar</div>
                    </div>
                    <div class="struk-total-val">
                        Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?>
                    </div>
                </div>

                <!-- Poin Dapat -->
                <?php if ($trx['poin_dapat'] > 0): ?>
                <div class="struk-poin">
                    🌟 Selamat! Kamu mendapat <strong>+<?= $trx['poin_dapat'] ?> poin</strong>
                    dari transaksi ini
                </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="struk-footer">
                    <div class="struk-footer-thanks">Terima Kasih! 🎀</div>
                    <div class="struk-footer-text">
                        Tunjukkan struk ini untuk klaim poin<br>
                        ✦ Envio Store © <?= date('Y') ?> ✦<br>
                        Member Loyalty System
                    </div>
                </div>

            </div>
            <!-- End Struk Card -->

            <!-- Tombol Aksi -->
            <div class="struk-actions">
                <button class="btn btn-pink" onclick="window.print()">
                    🖨️ Cetak Struk
                </button>
                <a href="transaksi.php" class="btn btn-outline">
                    ✦ Transaksi Baru
                </a>
                <a href="dashboard.php" class="btn btn-outline">
                    ◈ Dashboard
                </a>
            </div>

        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>
</body>
</html>