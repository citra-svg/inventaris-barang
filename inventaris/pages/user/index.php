<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Cek role (hanya admin)
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil semua user
$users = get("SELECT * FROM users ORDER BY id DESC");

// Proses Hapus User
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    if ($id == $_SESSION['user_id']) {
        echo "<script>
            alert('Anda tidak bisa menghapus akun sendiri!');
            window.location.href = 'index.php';
        </script>";
        exit();
    }
    
    $user = getOne("SELECT username FROM users WHERE id = $id");
    if ($user) {
        $sql = "DELETE FROM users WHERE id = $id";
        if (delete($sql)) {
            logAktivitas($_SESSION['user_id'], 'Hapus User', "Menghapus user: {$user['username']}");
            echo "<script>
                alert('User berhasil dihapus!');
                window.location.href = 'index.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menghapus user!');
                window.location.href = 'index.php';
            </script>";
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-people"></i> Kelola User</h4>
    <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah User
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php $no = 1; foreach ($users as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($item['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($item['nama_lengkap']); ?></td>
                                <td>
                                    <?php 
                                    $role = $item['role'];
                                    $badge_class = 'bg-secondary';
                                    if ($role == 'admin') $badge_class = 'bg-danger';
                                    elseif ($role == 'staff') $badge_class = 'bg-success';
                                    else $badge_class = 'bg-info';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst(htmlspecialchars($role)); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if ($item['id'] != $_SESSION['user_id']): ?>
                                        <a href="?hapus=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled title="Tidak bisa hapus sendiri">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada user</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>