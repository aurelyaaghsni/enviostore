<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php?error=akses_ditolak');
    exit();
}

require_once 'config/db.php';
require_once 'vendor/TCPDF-6.11.3/tcpdf.php';

$dari   = isset($_GET['dari'])   ? $_GET['dari']   : date('Y-m-01');
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');

$stmt = mysqli_prepare($conn, "
    SELECT t.*, m.nama as nama_member, a.nama as nama_kasir
    FROM transaksi t
    LEFT JOIN member m ON t.member_id = m.id
    LEFT JOIN admin a ON t.kasir_id = a.id
    WHERE DATE(t.created_at) BETWEEN ? AND ?
    ORDER BY t.created_at DESC
");
mysqli_stmt_bind_param($stmt, 'ss', $dari, $sampai);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rows   = mysqli_fetch_all($result, MYSQLI_ASSOC);

$total_trx    = count($rows);
$total_omzet  = array_sum(array_column($rows, 'total_bayar'));
$total_diskon = array_sum(array_column($rows, 'diskon'));

$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('Envio Store');
$pdf->SetAuthor('Envio Store Member System');
$pdf->SetTitle('Laporan Transaksi Envio Store');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// ===== HEADER =====
// Garis atas tebal
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, 15, 282, 15);

$pdf->Ln(3);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'ENVIO STORE', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 5, 'Member Loyalty System', 0, 1, 'C');
$pdf->Ln(2);

// Garis bawah header
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $pdf->GetY(), 282, $pdf->GetY());
$pdf->Ln(4);

// ===== JUDUL LAPORAN =====
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 7, 'LAPORAN TRANSAKSI', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 5, 'Periode: ' . date('d/m/Y', strtotime($dari)) . ' s/d ' . date('d/m/Y', strtotime($sampai)), 0, 1, 'C');
$pdf->Cell(0, 5, 'Dicetak: ' . date('d/m/Y H:i') . '  |  Oleh: ' . $_SESSION['nama'], 0, 1, 'C');
$pdf->Ln(5);

// ===== SUMMARY BOX =====
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetDrawColor(180, 180, 180);
$pdf->SetLineWidth(0.3);

$col = (297 - 30) / 3;
$pdf->Cell($col, 9, 'Total Transaksi: ' . $total_trx . ' trx', 1, 0, 'C', true);
$pdf->Cell($col, 9, 'Total Omzet: Rp ' . number_format($total_omzet, 0, ',', '.'), 1, 0, 'C', true);
$pdf->Cell($col, 9, 'Total Diskon: Rp ' . number_format($total_diskon, 0, ',', '.'), 1, 1, 'C', true);
$pdf->Ln(5);

// ===== HEADER TABEL =====
$pdf->SetFillColor(50, 50, 50);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetDrawColor(100, 100, 100);
$pdf->SetLineWidth(0.3);

$pdf->Cell(8,  8, '#',              1, 0, 'C', true);
$pdf->Cell(40, 8, 'Kode Transaksi', 1, 0, 'C', true);
$pdf->Cell(42, 8, 'Member',         1, 0, 'C', true);
$pdf->Cell(36, 8, 'Total Belanja',  1, 0, 'C', true);
$pdf->Cell(28, 8, 'Diskon',         1, 0, 'C', true);
$pdf->Cell(36, 8, 'Total Bayar',    1, 0, 'C', true);
$pdf->Cell(16, 8, 'Poin',           1, 0, 'C', true);
$pdf->Cell(30, 8, 'Kasir',          1, 0, 'C', true);
$pdf->Cell(31, 8, 'Tanggal',        1, 1, 'C', true);

// ===== ISI TABEL =====
$pdf->SetFont('helvetica', '', 8);
$no = 1;
foreach ($rows as $row) {
    if ($no % 2 === 0) {
        $pdf->SetFillColor(248, 248, 248);
        $fill = true;
    } else {
        $fill = false;
    }

    $pdf->SetTextColor(30, 30, 30);
    $pdf->Cell(8,  7, $no++,                                                      1, 0, 'C', $fill);
    $pdf->Cell(40, 7, $row['kode_transaksi'],                                     1, 0, 'C', $fill);
    $pdf->Cell(42, 7, $row['nama_member'] ?? '— Non Member —',                    1, 0, 'L', $fill);
    $pdf->Cell(36, 7, 'Rp ' . number_format($row['total_belanja'],  0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(28, 7, 'Rp ' . number_format($row['diskon'],         0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(36, 7, 'Rp ' . number_format($row['total_bayar'],    0, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell(16, 7, '+' . $row['poin_dapat'],                                   1, 0, 'C', $fill);
    $pdf->Cell(30, 7, $row['nama_kasir'] ?? '—',                                  1, 0, 'C', $fill);
    $pdf->Cell(31, 7, date('d/m/Y H:i', strtotime($row['created_at'])),           1, 1, 'C', $fill);
}

// ===== FOOTER TOTAL =====
$pdf->Ln(3);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $pdf->GetY(), 282, $pdf->GetY());
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 5, 'Total Omzet Periode: Rp ' . number_format($total_omzet, 0, ',', '.'), 0, 1, 'R');
$pdf->Cell(0, 5, 'Total Diskon Periode: Rp ' . number_format($total_diskon, 0, ',', '.'), 0, 1, 'R');

// Garis bawah footer
$pdf->Ln(3);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, $pdf->GetY(), 282, $pdf->GetY());
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 5, 'Dokumen ini digenerate otomatis oleh sistem Envio Store Member Loyalty System', 0, 1, 'C');

$pdf->Output('Laporan_Transaksi_Envio_Store_' . $dari . '_sd_' . $sampai . '.pdf', 'D');
?>