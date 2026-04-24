<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$rfid = isset($_GET['rfid']) ? trim($_GET['rfid']) : '';

if (!$rfid) {
    echo json_encode(['status' => 'error', 'message' => 'RFID kosong']);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT * FROM member WHERE rfid_id = ?");
mysqli_stmt_bind_param($stmt, 's', $rfid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Hitung diskon berdasarkan poin
    $poin = $row['total_poin'];
    if ($poin >= 1000)     $diskon = 15;
    elseif ($poin >= 500)  $diskon = 10;
    elseif ($poin >= 100)  $diskon = 5;
    else                   $diskon = 0;

    echo json_encode([
        'status'         => 'found',
        'id'             => $row['id'],
        'nama'           => $row['nama'],
        'no_hp'          => $row['no_hp'],
        'total_poin'     => $row['total_poin'],
        'total_belanja'  => $row['total_belanja'],
        'diskon_persen'  => $diskon,
    ]);
} else {
    echo json_encode(['status' => 'notfound', 'message' => 'Member tidak ditemukan']);
}
?>