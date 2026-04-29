<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: transaksi.php');
    exit();
}

$stmt = mysqli_prepare($conn, "
    SELECT t.*, m.nama as nama_member, m.no_hp, m.rfid_id,
           m.total_poin as poin_sekarang,
           a.nama as nama_kasir, a.role as role_kasir
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

$diskon_persen = $trx['total_belanja'] > 0
    ? round(($trx['diskon'] / $trx['total_belanja']) * 100)
    : 0;

// Nama kasir sesuai role
$nama_kasir_tampil = $trx['role_kasir'] === 'admin' ? 'admin kalcer' : 'kasir cantik';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* ===== PRINT THERMAL 80MM ===== */
        @media print {
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { background: #fff !important; }
            .sidebar, .topbar, .struk-actions { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .struk-wrapper { max-width: 80mm !important; margin: 0 auto !important; }
            .struk-card {
                background: #fff !important;
                border: none !important;
                box-shadow: none !important;
                padding: 4mm !important;
                border-radius: 0 !important;
                animation: none !important;
            }
            .thermal-text {
                font-family: 'Courier New', monospace !important;
                font-size: 9pt !important;
                color: #000 !important;
                line-height: 1.4 !important;
            }
            .thermal-center { text-align: center !important; }
            .thermal-bold { font-weight: bold !important; }
            .thermal-divider {
                border-top: 1px dashed #000 !important;
                margin: 3mm 0 !important;
            }
            .thermal-row {
                display: flex !important;
                justify-content: space-between !important;
                font-size: 8pt !important;
            }
            .thermal-total {
                font-size: 11pt !important;
                font-weight: bold !important;
                text-align: center !important;
            }
            .thermal-logo { display: block !important; }
            .screen-only { display: none !important; }
        }

        @media screen {
            .thermal-logo { display: none; }
        }
    </style>
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

            <div class="struk-card" id="strukCetak">

                <!-- Logo (muncul saat print) -->
                <div class="thermal-logo" style="text-align:center; margin-bottom:3mm;">
                    <svg width="40" height="40" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="36" cy="36" r="35" fill="none" stroke="#000" stroke-width="2"/>
                        <ellipse cx="36" cy="36" rx="16" ry="10" fill="none" stroke="#000" stroke-width="1.5"/>
                        <ellipse cx="36" cy="36" rx="10" ry="16" fill="none" stroke="#000" stroke-width="1.5"/>
                        <circle cx="36" cy="36" r="5" fill="#000"/>
                        <circle cx="36" cy="20" r="2.5" fill="#000"/>
                        <circle cx="36" cy="52" r="2.5" fill="#000"/>
                        <circle cx="20" cy="36" r="2.5" fill="#000"/>
                        <circle cx="52" cy="36" r="2.5" fill="#000"/>
                    </svg>
                </div>

                <!-- Header Struk (di screen pakai styling cantik) -->
                <div class="struk-header screen-only">
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

                <!-- Konten Thermal (muncul saat print) -->
                <div class="thermal-text" style="display:none;" id="thermalContent">
                    <div class="thermal-center thermal-bold" style="font-size:11pt;">ENVIO STORE</div>
                    <div class="thermal-center" style="font-size:8pt;">Member Loyalty System</div>
                    <div class="thermal-center" style="font-size:7pt;">================================</div>

                    <div style="font-size:8pt; margin-top:2mm;">
                        Kode  : <?= $trx['kode_transaksi'] ?><br>
                        Kasir : <?= $nama_kasir_tampil ?><br>
                        Tgl   : <?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?>
                    </div>

                    <div class="thermal-center" style="font-size:7pt;">--------------------------------</div>

                    <?php if ($trx['nama_member']): ?>
                    <div style="font-size:8pt;">
                        Member: <?= htmlspecialchars($trx['nama_member']) ?><br>
                        Poin  : ⭐ <?= $trx['poin_sekarang'] ?> poin
                    </div>
                    <div class="thermal-center" style="font-size:7pt;">--------------------------------</div>
                    <?php endif; ?>

                    <div style="font-size:8pt;">
                        <div class="thermal-row">
                            <span>Total Belanja</span>
                            <span>Rp <?= number_format($trx['total_belanja'], 0, ',', '.') ?></span>
                        </div>
                        <div class="thermal-row">
                            <span>Diskon <?= $diskon_persen > 0 ? "($diskon_persen%)" : '' ?></span>
                            <span>Rp <?= number_format($trx['diskon'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="thermal-center" style="font-size:7pt;">--------------------------------</div>
                    <div class="thermal-total">TOTAL BAYAR</div>
                    <div class="thermal-total">Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?></div>
                    <div class="thermal-center" style="font-size:7pt;">--------------------------------</div>

                    <?php if ($trx['poin_dapat'] > 0): ?>
                    <div class="thermal-center" style="font-size:8pt; margin: 2mm 0;">
                        yeyy hari ini kamu dapett +<?= $trx['poin_dapat'] ?> poin !!
                    </div>
                    <div class="thermal-center" style="font-size:7pt;">================================</div>
                    <?php endif; ?>

                    <div class="thermal-center" style="font-size:8pt; margin-top:2mm;">
                        maaciiw sudah belanja disiniii!! <br>
                        jangan lupaa datang lagiiii yyaaw &lt;333
                    </div>
                    <div class="thermal-center" style="font-size:7pt; margin-top:2mm;">
                        ✦ Envio Store © <?= date('Y') ?> ✦
                    </div>
                </div>

                <!-- Info Transaksi (tampilan screen) -->
                <div class="screen-only">
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
                            <span class="struk-row-val"><?= $nama_kasir_tampil ?></span>
                        </div>
                    </div>

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

                    <div class="struk-section">
                        <div class="struk-section-title">✦ Rincian Belanja</div>
                        <div class="struk-row">
                            <span class="struk-row-label">Total Belanja</span>
                            <span class="struk-row-val">Rp <?= number_format($trx['total_belanja'], 0, ',', '.') ?></span>
                        </div>
                        <div class="struk-row">
                            <span class="struk-row-label">Diskon <?= $diskon_persen > 0 ? "($diskon_persen%)" : '' ?></span>
                            <span class="struk-row-val pink">— Rp <?= number_format($trx['diskon'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <div class="struk-total">
                        <div>
                            <div class="struk-total-label">Total Bayar</div>
                        </div>
                        <div class="struk-total-val">
                            Rp <?= number_format($trx['total_bayar'], 0, ',', '.') ?>
                        </div>
                    </div>

                    <?php if ($trx['poin_dapat'] > 0): ?>
                    <div class="struk-poin">
                        🌟 yeyy hari ini kamu dapett <strong>+<?= $trx['poin_dapat'] ?> poin</strong> !!
                    </div>
                    <?php endif; ?>

                    <div class="struk-footer">
                        <div class="struk-footer-thanks">maaciiw sudah belanja disiniii!! 🎀</div>
                        <div class="struk-footer-text">
                            jangan lupaa datang lagiiii yyaaw &lt;333<br>
                            ✦ Envio Store © <?= date('Y') ?> ✦
                        </div>
                    </div>
                </div>

            </div>

            <!-- Tombol Aksi -->
            <div class="struk-actions">
                <button class="btn btn-pink" onclick="cetakThermal()">
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

<script>
function cetakThermal() {
    // Tampilkan konten thermal, sembunyikan screen content
    document.getElementById('thermalContent').style.display = 'block';
    window.print();
    // Sembunyikan lagi setelah print
    setTimeout(() => {
        document.getElementById('thermalContent').style.display = 'none';
    }, 1000);
}
</script>

<script src="assets/js/script.js"></script>
</body>
</html>