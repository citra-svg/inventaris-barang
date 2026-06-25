<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$user_id = $_SESSION['user_id'];
$user = getOne("SELECT * FROM users WHERE id = $user_id");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($nama_lengkap)) {
        $error = 'Nama lengkap wajib diisi!';
    } else {
        $sql = "UPDATE users SET nama_lengkap = '$nama_lengkap' WHERE id = $user_id";
        
        if (!empty($password_baru)) {
            if ($password_baru !== $confirm_password) {
                $error = 'Password baru dan konfirmasi password tidak cocok!';
            } elseif (strlen($password_baru) < 6) {
                $error = 'Password minimal 6 karakter!';
            } else {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama_lengkap = '$nama_lengkap', password = '$password_hash' WHERE id = $user_id";
            }
        }
        
        if (empty($error)) {
            if (update($sql)) {
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $success = 'Profil berhasil diupdate!';
                $user = getOne("SELECT * FROM users WHERE id = $user_id");
                
                // Log aktivitas
                logAktivitas($user_id, 'Update Profil', 'Mengupdate profil user');
            } else {
                $error = 'Gagal mengupdate profil: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-circle"></i> Profil Saya</h4>
    <a href="../dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small class="text-muted">Username tidak bisa diubah</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Role</label>
                    <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    <small class="text-muted">Role tidak bisa diubah</small>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak diubah" minlength="6">
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Profil
                    </button>
                    <a href="../dashboard.php" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
        
        <hr>
        <div class="row text-muted small">
            <div class="col-md-6">
                <i class="bi bi-calendar"></i> Bergabung: <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
            </div>
            <div class="col-md-6 text-md-end">
                <i class="bi bi-shield-lock"></i> Terakhir login: <?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>