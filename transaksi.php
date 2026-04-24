<?php
session_start();
require_once 'includes/header.php';
require_once 'config/db.php';

$success = '';
$error   = '';

// ===== SIMPAN TRANSAKSI =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id    = !empty($_POST['member_id']) ? (int)$_POST['member_id'] : null;
    $total_belanja = (float) str_replace(['.', ','], ['', '.'], $_POST['total_belanja']);
    $diskon_persen = (float) $_POST['diskon_persen'];
    $diskon_rp     = $total_belanja * ($diskon_persen / 100);
    $total_bayar   = $total_belanja - $diskon_rp;
    $poin_dapat    = floor($total_belanja / 10000);
    $kasir_id      = $_SESSION['user'];

    // Generate kode transaksi unik
    $kode = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

    // Simpan transaksi
    $stmt = mysqli_prepare($conn,
        "INSERT INTO transaksi (kode_transaksi, member_id, total_belanja, diskon, total_bayar, poin_dapat, kasir_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, 'sidddii', $kode, $member_id, $total_belanja, $diskon_rp, $total_bayar, $poin_dapat, $kasir_id);
    mysqli_stmt_execute($stmt);
    $transaksi_id = mysqli_insert_id($conn);

    // Update poin & total belanja member kalau ada member
    if ($member_id) {
        $upd = mysqli_prepare($conn,
            "UPDATE member SET total_poin = total_poin + ?, total_belanja = total_belanja + ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($upd, 'idi', $poin_dapat, $total_belanja, $member_id);
        mysqli_stmt_execute($upd);

        // Simpan detail poin
        $dp = mysqli_prepare($conn,
            "INSERT INTO detail_poin (member_id, transaksi_id, poin, keterangan) VALUES (?, ?, ?, ?)"
        );
        $ket = "Belanja Rp " . number_format($total_belanja, 0, ',', '.');
        mysqli_stmt_bind_param($dp, 'iiis', $member_id, $transaksi_id, $poin_dapat, $ket);
        mysqli_stmt_execute($dp);
    }

    // Redirect ke struk
    header("Location: struk.php?id=$transaksi_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">

        <div class="topbar">
            <div class="topbar-title">✦ Input Transaksi</div>
            <div class="topbar-date">🎀 <?= date('d F Y') ?></div>
        </div>

        <form method="POST" id="formTransaksi">
        <input type="hidden" name="member_id"    id="hidden_member_id">
        <input type="hidden" name="diskon_persen" id="hidden_diskon">

        <!-- RFID Scanner -->
        <div class="rfid-scanner">
            <div class="rfid-scanner-title">✦ Tap / Input RFID Card Member ✦</div>
            <div class="rfid-input-wrap">
                <input type="text" id="rfid_scan" class="form-control"
                       placeholder="Scan atau ketik RFID ID..."
                       style="text-align:center; letter-spacing:2px;">
                <button type="button" class="btn btn-pink" onclick="cariMember()">Cari</button>
                <button type="button" class="btn btn-outline" onclick="resetMember()">Reset</button>
            </div>
            <div class="rfid-status" id="rfid_status">
                ✦ Masukkan RFID untuk identifikasi member
            </div>
        </div>

        <!-- Info Member (muncul kalau ketemu) -->
        <div class="member-info-card" id="memberCard">
            <div class="member-info-header">
                <div class="member-info-avatar" id="memberAvatar">?</div>
                <div>
                    <div class="member-info-name" id="memberNama">—</div>
                    <div class="member-info-sub" id="memberHp">—</div>
                </div>
                <span class="badge badge-pink" style="margin-left:auto;" id="memberDiskon">0% Diskon</span>
            </div>
            <div class="member-info-stats">
                <div class="member-stat">
                    <div class="member-stat-label">Total Poin</div>
                    <div class="member-stat-val" id="memberPoin">0</div>
                </div>
                <div class="member-stat">
                    <div class="member-stat-label">Diskon</div>
                    <div class="member-stat-val" id="memberDiskonPct">0%</div>
                </div>
                <div class="member-stat">
                    <div class="member-stat-label">Total Belanja</div>
                    <div class="member-stat-val" id="memberBelanja">Rp 0</div>
                </div>
            </div>
        </div>

        <!-- Input Belanja -->
        <div class="section-card">
            <div class="section-title">🎀 Detail Belanja</div>

            <div class="calc-grid">
                <div class="form-group">
                    <label class="form-label">Total Belanja (Rp)</label>
                    <input type="text" id="input_belanja" class="form-control"
                           placeholder="0" oninput="hitungOtomatis(this.value)"
                           style="font-size:18px; font-weight:700;">
                    <input type="hidden" name="total_belanja" id="hidden_belanja">
                </div>
                <div class="form-group">
                    <label class="form-label">Poin yang Akan Didapat</label>
                    <input type="text" id="preview_poin" class="form-control"
                           placeholder="—" readonly
                           style="color:#ff69b4; font-weight:700;">
                </div>
            </div>

            <!-- Hasil Kalkulasi -->
            <div class="calc-result">
                <div class="calc-row">
                    <span class="calc-row-label">Total Belanja</span>
                    <span class="calc-row-val" id="res_belanja">Rp 0</span>
                </div>
                <div class="calc-row">
                    <span class="calc-row-label">Diskon Member</span>
                    <span class="calc-row-val pink" id="res_diskon">— Rp 0</span>
                </div>
                <div class="calc-row total-row">
                    <span class="calc-row-label">TOTAL BAYAR</span>
                    <span class="calc-row-val" id="res_total">Rp 0</span>
                </div>
            </div>

            <button type="submit" class="btn btn-pink" style="width:100%; padding:14px; font-size:14px;">
                ✦ Simpan Transaksi & Cetak Struk
            </button>
        </div>

        </form>
    </div>
</div>

<script>
let diskonPersen = 0;

// Cari member by RFID
async function cariMember() {
    const rfid = document.getElementById('rfid_scan').value.trim();
    if (!rfid) return;

    const status = document.getElementById('rfid_status');
    status.className = 'rfid-status';
    status.textContent = '🔍 Mencari member...';

    const res  = await fetch(`api/member.php?rfid=${encodeURIComponent(rfid)}`);
    const data = await res.json();

    if (data.status === 'found') {
        // Tampilkan info member
        diskonPersen = data.diskon_persen;
        document.getElementById('hidden_member_id').value = data.id;
        document.getElementById('hidden_diskon').value    = diskonPersen;

        document.getElementById('memberAvatar').textContent  = data.nama.charAt(0).toUpperCase();
        document.getElementById('memberNama').textContent    = data.nama;
        document.getElementById('memberHp').textContent     = data.no_hp || '—';
        document.getElementById('memberPoin').textContent   = data.total_poin + ' poin';
        document.getElementById('memberDiskonPct').textContent = data.diskon_persen + '%';
        document.getElementById('memberDiskon').textContent = data.diskon_persen + '% Diskon';
        document.getElementById('memberBelanja').textContent =
            'Rp ' + parseInt(data.total_belanja).toLocaleString('id-ID');

        document.getElementById('memberCard').classList.add('visible');

        status.className = 'rfid-status found';
        status.textContent = '✦ Member ditemukan! Data berhasil dimuat.';

        // Hitung ulang kalau sudah ada input belanja
        hitungOtomatis(document.getElementById('input_belanja').value);

    } else {
        status.className = 'rfid-status notfound';
        status.textContent = '✕ Member tidak ditemukan — lanjut sebagai non-member';
        resetMember();
    }
}

// Reset member
function resetMember() {
    diskonPersen = 0;
    document.getElementById('hidden_member_id').value = '';
    document.getElementById('hidden_diskon').value    = 0;
    document.getElementById('memberCard').classList.remove('visible');
    document.getElementById('rfid_scan').value = '';
    hitungOtomatis(document.getElementById('input_belanja').value);
}

// Hitung otomatis
function hitungOtomatis(val) {
    // Format angka
    val = val.replace(/\D/g, '');
    document.getElementById('input_belanja').value  = parseInt(val || 0).toLocaleString('id-ID');
    document.getElementById('hidden_belanja').value = val || 0;

    const belanja    = parseInt(val || 0);
    const diskonRp   = Math.floor(belanja * (diskonPersen / 100));
    const totalBayar = belanja - diskonRp;
    const poin       = Math.floor(belanja / 10000);

    document.getElementById('res_belanja').textContent  = 'Rp ' + belanja.toLocaleString('id-ID');
    document.getElementById('res_diskon').textContent   = '— Rp ' + diskonRp.toLocaleString('id-ID');
    document.getElementById('res_total').textContent    = 'Rp ' + totalBayar.toLocaleString('id-ID');
    document.getElementById('preview_poin').value       = '+' + poin + ' poin';
}

// Enter di RFID input = langsung cari
document.getElementById('rfid_scan').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); cariMember(); }
});
</script>

<script src="assets/js/script.js"></script>
</body>
</html>