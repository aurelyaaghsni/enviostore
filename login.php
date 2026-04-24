<?php
session_start();

// Kalau sudah login, langsung ke dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/db.php';

    $username = trim($_POST['username']);
    $password = md5(trim($_POST['password'])); // MD5 sama kayak waktu insert data
    $role     = $_POST['role'];

    // Query cek user
    $sql    = "SELECT * FROM admin WHERE username = ? AND password = ? AND role = ?";
    $stmt   = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sss', $username, $password, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Login berhasil — simpan ke session
        $_SESSION['user']     = $row['id'];
        $_SESSION['nama']     = $row['nama'];
        $_SESSION['role']     = $row['role'];
        $_SESSION['username'] = $row['username'];

        header('Location: dashboard.php');
        exit();
    } else {
        $error = '✦ Username, password, atau role salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Envio Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-page">

    <!-- Background effects -->
    <div class="dot-grid"></div>
    <div class="aurora-blob blob1"></div>
    <div class="aurora-blob blob2"></div>
    <div class="aurora-blob blob3"></div>

    <!-- Sparkle -->
    <span class="sparkle" style="top:12%;left:8%;">✦</span>
    <span class="sparkle" style="top:20%;right:10%;">✧</span>
    <span class="sparkle" style="bottom:15%;left:12%;">✦</span>
    <span class="sparkle" style="bottom:25%;right:8%;">✧</span>
    <span class="sparkle" style="top:60%;left:5%;">✦</span>
    <span class="sparkle" style="top:50%;right:5%;">✧</span>

    <!-- Particles -->
    <div class="particle" style="left:10%;bottom:5%;"></div>
    <div class="particle" style="left:30%;bottom:3%;"></div>
    <div class="particle" style="left:60%;bottom:6%;"></div>
    <div class="particle" style="left:80%;bottom:4%;"></div>

    <!-- Card -->
    <div class="card-border-wrap">
        <div class="login-card">

            <!-- Logo -->
            <div class="logo-area">
                <div class="logo-svg">
                    <svg width="72" height="72" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="bgGrad" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" stop-color="#2a0040"/>
                                <stop offset="100%" stop-color="#0f0018"/>
                            </radialGradient>
                            <linearGradient id="pinkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ff69b4"/>
                                <stop offset="50%" stop-color="#ff1493"/>
                                <stop offset="100%" stop-color="#cc00aa"/>
                            </linearGradient>
                            <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#ffb6d9"/>
                                <stop offset="100%" stop-color="#ff69b4"/>
                            </linearGradient>
                        </defs>
                        <circle cx="36" cy="36" r="35" fill="url(#bgGrad)" stroke="#ff149366" stroke-width="1"/>
                        <circle cx="36" cy="36" r="28" fill="none" stroke="#ff149333" stroke-width="0.5"/>
                        <ellipse cx="36" cy="36" rx="16" ry="10" fill="none" stroke="url(#pinkGrad)" stroke-width="1.5"/>
                        <ellipse cx="36" cy="36" rx="10" ry="16" fill="none" stroke="url(#pinkGrad)" stroke-width="1.5"/>
                        <circle cx="36" cy="36" r="5" fill="url(#pinkGrad)"/>
                        <circle cx="36" cy="20" r="2.5" fill="url(#goldGrad)"/>
                        <circle cx="36" cy="52" r="2.5" fill="url(#goldGrad)"/>
                        <circle cx="20" cy="36" r="2.5" fill="url(#goldGrad)"/>
                        <circle cx="52" cy="36" r="2.5" fill="url(#goldGrad)"/>
                    </svg>
                </div>
                <span class="brand-name">Envio Store</span>
                <div class="brand-sub">🎀 Member System 🎀</div>
            </div>

            <!-- Error message -->
            <?php if ($error): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <!-- Form login -->
            <form method="POST" action="">

                <div class="input-group">
                    <label class="input-label">Role</label>
                    <select name="role" class="role-select">
                        <option value="admin">✦ Admin</option>
                        <option value="kasir">✦ Kasir</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="input-label">Username</label>
                    <input type="text" name="username" class="input-field"
                           placeholder="Masukkan username..." required>
                </div>

                <div class="input-group">
                    <label class="input-label">Password</label>
                    <input type="password" name="password" class="input-field"
                           placeholder="••••••••" required>
                </div>

                <button type="submit" class="login-btn">✦ Masuk ✦</button>

            </form>

            <hr class="divider">
            <div class="footer-text">✦ Envio Store &copy; 2025 ✦</div>

        </div>
    </div>

</div>

<!-- Animasi sparkle pakai JS -->
<script src="assets/js/script.js"></script>
</body>
</html>