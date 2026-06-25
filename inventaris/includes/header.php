<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/inventaris/config/database.php';

$user_role = $_SESSION['role'] ?? 'staff';
$user_name = $_SESSION['nama_lengkap'] ?? 'Pengguna';
$user_id = $_SESSION['user_id'];

// Hitung notifikasi
$total_notif = 0;
$stok_menipis = getOne("SELECT COUNT(*) as total FROM barang WHERE stok <= min_stok AND stok > 0");
$stok_habis = getOne("SELECT COUNT(*) as total FROM barang WHERE stok = 0");
$total_notif = ($stok_menipis['total'] ?? 0) + ($stok_habis['total'] ?? 0);

// Tentukan base path
$base_path = '';
$script_path = $_SERVER['SCRIPT_NAME'];
if (strpos($script_path, '/pages/barang/') !== false || 
    strpos($script_path, '/pages/kategori/') !== false ||
    strpos($script_path, '/pages/masuk/') !== false ||
    strpos($script_path, '/pages/keluar/') !== false ||
    strpos($script_path, '/pages/laporan/') !== false ||
    strpos($script_path, '/pages/profil/') !== false ||
    strpos($script_path, '/pages/log/') !== false ||
    strpos($script_path, '/pages/user/') !== false ||
    strpos($script_path, '/pages/penjualan/') !== false ||
    strpos($script_path, '/pages/peminjaman/') !== false ||
    strpos($script_path, '/pages/pengembalian/') !== false) {
    $base_path = '../../';
} elseif (strpos($script_path, '/pages/') !== false) {
    $base_path = '../';
}

// Fungsi untuk cek menu aktif
function isActive($path) {
    return strpos($_SERVER['PHP_SELF'], $path) !== false ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Inventaris Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $base_path; ?>pages/dashboard.php">
                <i class="bi bi-box-seam"></i> Inventaris
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- DASHBOARD -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('dashboard.php'); ?>" href="<?php echo $base_path; ?>pages/dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    <!-- BARANG -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/barang/'); ?>" href="<?php echo $base_path; ?>pages/barang/index.php">
                            <i class="bi bi-box"></i> Barang
                        </a>
                    </li>

                    <!-- KATEGORI -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/kategori/'); ?>" href="<?php echo $base_path; ?>pages/kategori/index.php">
                            <i class="bi bi-tags"></i> Kategori
                        </a>
                    </li>

                    <!-- PEMINJAMAN -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/peminjaman/'); ?>" href="<?php echo $base_path; ?>pages/peminjaman/index.php">
                            <i class="bi bi-hand-index-thumb"></i> Peminjaman
                        </a>
                    </li>

                    <!-- PENGEMBALIAN -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/pengembalian/'); ?>" href="<?php echo $base_path; ?>pages/pengembalian/index.php">
                            <i class="bi bi-arrow-return-left"></i> Pengembalian
                        </a>
                    </li>

                    <!-- MASUK (HANYA ADMIN) -->
                    <?php if ($user_role == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/masuk/'); ?>" href="<?php echo $base_path; ?>pages/masuk/index.php">
                            <i class="bi bi-arrow-down-circle"></i> Masuk
                        </a>
                    </li>

                    <!-- KELUAR (HANYA ADMIN) -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/keluar/'); ?>" href="<?php echo $base_path; ?>pages/keluar/index.php">
                            <i class="bi bi-arrow-up-circle"></i> Keluar
                        </a>
                    </li>

                    <!-- LAPORAN (HANYA ADMIN) -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/laporan/'); ?>" href="<?php echo $base_path; ?>pages/laporan/index.php">
                            <i class="bi bi-file-text"></i> Laporan
                        </a>
                    </li>
                    <?php endif; ?>

                    <!-- TRANSAKSI (SEMUA USER) -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo isActive('/penjualan/'); ?>" href="<?php echo $base_path; ?>pages/penjualan/index.php">
                            <i class="bi bi-cart"></i> Transaksi
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <!-- NOTIFIKASI -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo $base_path; ?>pages/barang/index.php?stok=menipis">
                            <i class="bi bi-bell"></i>
                            <?php if ($total_notif > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $total_notif; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- DARK MODE -->
                    <li class="nav-item">
                        <button class="btn btn-link nav-link" onclick="toggleDarkMode()" title="Toggle Dark Mode" style="color: rgba(255,255,255,0.8);">
                            <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                        </button>
                    </li>

                    <!-- USER DROPDOWN -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user_name); ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo ucfirst($user_role); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>pages/profil/index.php">
                                <i class="bi bi-person"></i> Profil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>pages/log/index.php">
                                <i class="bi bi-clock-history"></i> Log Aktivitas
                            </a></li>
                            <?php if ($user_role == 'admin'): ?>
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>pages/user/index.php">
                                    <i class="bi bi-people"></i> Kelola User
                                </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base_path; ?>logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">