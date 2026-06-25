<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Jika admin, bisa lihat semua log. Jika staff, hanya log miliknya sendiri
if ($user_role == 'admin') {
    $logs = get("
        SELECT l.*, u.username, u.nama_lengkap 
        FROM log_aktivitas l 
        JOIN users u ON l.user_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT 200
    ");
} else {
    // Staff hanya melihat log miliknya sendiri
    $logs = get("
        SELECT l.*, u.username, u.nama_lengkap 
        FROM log_aktivitas l 
        JOIN users u ON l.user_id = u.id 
        WHERE l.user_id = $user_id
        ORDER BY l.created_at DESC 
        LIMIT 200
    ");
}

// Hapus semua log (hanya admin)
if (isset($_GET['hapus_semua']) && $user_role == 'admin') {
    $sql = "DELETE FROM log_aktivitas";
    if (delete($sql)) {
        logAktivitas($user_id, 'Hapus Semua Log', 'Menghapus semua log aktivitas');
        echo "<script>
            alert('Semua log berhasil dihapus!');
            window.location.href = 'index.php';
        </script>";
    }
}

// Hapus log milik sendiri (untuk staff)
if (isset($_GET['hapus_sendiri']) && $user_role == 'staff') {
    $sql = "DELETE FROM log_aktivitas WHERE user_id = $user_id";
    if (delete($sql)) {
        echo "<script>
            alert('Log aktivitas Anda berhasil dihapus!');
            window.location.href = 'index.php';
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-clock-history"></i> Log Aktivitas</h4>
    <div>
        <?php if ($user_role == 'admin'): ?>
            <a href="?hapus_semua=yes" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus semua log?')">
                <i class="bi bi-trash"></i> Hapus Semua
            </a>
        <?php else: ?>
            <a href="?hapus_sendiri=yes" class="btn btn-warning" onclick="return confirm('Yakin ingin menghapus semua log aktivitas Anda?')">
                <i class="bi bi-trash"></i> Hapus Log Saya
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($user_role == 'staff'): ?>
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle"></i> Anda hanya dapat melihat log aktivitas Anda sendiri.
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th>IP Address</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) > 0): ?>
                        <?php $no = 1; foreach ($logs as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['nama_lengkap']); ?></strong>
                                    <br><small class="text-muted">@<?php echo htmlspecialchars($item['username']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($item['aktivitas']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($item['detail'] ?? '-'); ?></td>
                                <td><code><?php echo htmlspecialchars($item['ip_address']); ?></code></td>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($item['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada log aktivitas
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-muted small mt-2">
            Total: <?php echo count($logs); ?> log aktivitas
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>