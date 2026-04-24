<nav class="sidebar">
    <div class="sidebar-logo">
        <svg width="36" height="36" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <radialGradient id="bgG" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#2a0040"/>
                    <stop offset="100%" stop-color="#0f0018"/>
                </radialGradient>
                <linearGradient id="pG" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#ff69b4"/>
                    <stop offset="100%" stop-color="#ff1493"/>
                </linearGradient>
            </defs>
            <circle cx="36" cy="36" r="35" fill="url(#bgG)" stroke="#ff149366" stroke-width="1"/>
            <ellipse cx="36" cy="36" rx="16" ry="10" fill="none" stroke="url(#pG)" stroke-width="1.5"/>
            <ellipse cx="36" cy="36" rx="10" ry="16" fill="none" stroke="url(#pG)" stroke-width="1.5"/>
            <circle cx="36" cy="36" r="5" fill="url(#pG)"/>
        </svg>
        <span>Envio Store</span>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
        <div>
            <div class="user-name"><?= $_SESSION['nama'] ?></div>
            <div class="user-role">✦ <?= ucfirst($_SESSION['role']) ?></div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <span class="menu-icon">◈</span> Dashboard
            </a>
        </li>
        <li>
            <a href="member.php" class="<?= basename($_SERVER['PHP_SELF']) == 'member.php' ? 'active' : '' ?>">
                <span class="menu-icon">◈</span> Member
            </a>
        </li>
        <li>
            <a href="transaksi.php" class="<?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : '' ?>">
                <span class="menu-icon">◈</span> Transaksi
            </a>
        </li>
        <li>
            <a href="riwayat.php" class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : '' ?>">
                <span class="menu-icon">◈</span> Riwayat
            </a>
        </li>
    </ul>

    <a href="logout.php" class="sidebar-logout">✦ Keluar</a>
</nav>