<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Cek role (hanya admin)
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$error = '';
$success = '';

// Ambil semua role yang sudah ada (untuk dropdown)
$existing_roles = get("SELECT DISTINCT role FROM users ORDER BY role");
$roles_list = [];
foreach ($existing_roles as $r) {
    if (!empty($r['role'])) {
        $roles_list[] = $r['role'];
    }
}
// Tambahkan role default jika belum ada
if (!in_array('admin', $roles_list)) $roles_list[] = 'admin';
if (!in_array('staff', $roles_list)) $roles_list[] = 'staff';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $role_baru = $_POST['role_baru'] ?? '';
    
    // Jika pilih "Lainnya...", gunakan role_baru
    if ($role == 'custom') {
        $role = trim($role_baru);
        if (empty($role)) {
            $error = 'Silakan masukkan nama role!';
        }
    }
    
    if (empty($username) || empty($password) || empty($nama_lengkap)) {
        $error = 'Semua field wajib diisi!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif (empty($role)) {
        $error = 'Role wajib dipilih!';
    } else {
        // Cek username duplikat
        $cek = getOne("SELECT id FROM users WHERE username = '$username'");
        if ($cek) {
            $error = 'Username sudah digunakan!';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role, nama_lengkap) 
                    VALUES ('$username', '$password_hash', '$role', '$nama_lengkap')";
            
            if (insert($sql)) {
                logAktivitas($_SESSION['user_id'], 'Tambah User', "Menambahkan user: $username (Role: $role)");
                $success = 'User berhasil ditambahkan!';
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal menambahkan user: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-person-plus"></i> Tambah User</h4>
    <a href="index.php" class="btn btn-secondary">
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
        
        <form method="POST" id="formTambahUser">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" id="roleSelect" onchange="toggleRoleInput()" required>
                        <option value="">Pilih Role</option>
                        <?php foreach ($roles_list as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>"><?php echo ucfirst(htmlspecialchars($r)); ?></option>
                        <?php endforeach; ?>
                        <option value="custom">Lainnya...</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="roleBaruContainer" style="display:none;">
                    <label class="form-label fw-semibold">Nama Role <span class="text-danger">*</span></label>
                    <input type="text" name="role_baru" class="form-control" placeholder="Contoh: manager, supervisor, dll" id="roleBaruInput">
                    <small class="text-muted">Masukkan nama role</small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRoleInput() {
    var select = document.getElementById('roleSelect');
    var container = document.getElementById('roleBaruContainer');
    var input = document.getElementById('roleBaruInput');
    
    if (select.value === 'custom') {
        container.style.display = 'block';
        input.required = true;
        input.focus();
    } else {
        container.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

document.getElementById('formTambahUser').addEventListener('submit', function(e) {
    var select = document.getElementById('roleSelect');
    var input = document.getElementById('roleBaruInput');
    
    if (select.value === 'custom' && input.value.trim() === '') {
        e.preventDefault();
        alert('Silakan masukkan nama role!');
        input.focus();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>