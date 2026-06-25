<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Cek role (hanya admin)
if ($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$user = getOne("SELECT * FROM users WHERE id = $id");
if (!$user) {
    header("Location: index.php");
    exit();
}

// Ambil semua role yang sudah ada
$existing_roles = get("SELECT DISTINCT role FROM users ORDER BY role");
$roles_list = [];
foreach ($existing_roles as $r) {
    if (!empty($r['role'])) {
        $roles_list[] = $r['role'];
    }
}
if (!in_array('admin', $roles_list)) $roles_list[] = 'admin';
if (!in_array('staff', $roles_list)) $roles_list[] = 'staff';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $role_baru = $_POST['role_baru'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    
    if ($role == 'custom') {
        $role = trim($role_baru);
        if (empty($role)) {
            $error = 'Silakan masukkan nama role!';
        }
    }
    
    if (empty($nama_lengkap)) {
        $error = 'Nama lengkap wajib diisi!';
    } elseif (empty($role)) {
        $error = 'Role wajib dipilih!';
    } else {
        $sql = "UPDATE users SET nama_lengkap = '$nama_lengkap', role = '$role' WHERE id = $id";
        
        if (!empty($password_baru)) {
            if (strlen($password_baru) < 6) {
                $error = 'Password minimal 6 karakter!';
            } else {
                $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama_lengkap = '$nama_lengkap', role = '$role', password = '$password_hash' WHERE id = $id";
            }
        }
        
        if (empty($error)) {
            if (update($sql)) {
                logAktivitas($_SESSION['user_id'], 'Edit User', "Mengedit user: {$user['username']} (Role: $role)");
                $success = 'User berhasil diupdate!';
                $user = getOne("SELECT * FROM users WHERE id = $id");
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal mengupdate user: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil"></i> Edit User</h4>
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
        
        <form method="POST" id="formEditUser">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    <small class="text-muted">Username tidak bisa diubah</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" id="roleSelect" onchange="toggleRoleInput()" required>
                        <option value="">Pilih Role</option>
                        <?php foreach ($roles_list as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $user['role'] == $r ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($r)); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="custom">Lainnya...</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="roleBaruContainer" style="display:none;">
                    <label class="form-label fw-semibold">Nama Role <span class="text-danger">*</span></label>
                    <input type="text" name="role_baru" class="form-control" placeholder="Contoh: manager, supervisor, dll" id="roleBaruInput">
                    <small class="text-muted">Masukkan nama role</small>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak diubah" minlength="6">
                    <small class="text-muted">Minimal 6 karakter</small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
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

document.getElementById('formEditUser').addEventListener('submit', function(e) {
    var select = document.getElementById('roleSelect');
    var input = document.getElementById('roleBaruInput');
    
    if (select.value === 'custom' && input.value.trim() === '') {
        e.preventDefault();
        alert('Silakan masukkan nama role!');
        input.focus();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('roleSelect');
    var currentRole = '<?php echo $user['role']; ?>';
    var options = [];
    for (var i = 0; i < select.options.length; i++) {
        options.push(select.options[i].value);
    }
    if (!options.includes(currentRole) && currentRole != '') {
        select.value = 'custom';
        document.getElementById('roleBaruContainer').style.display = 'block';
        document.getElementById('roleBaruInput').value = currentRole;
        document.getElementById('roleBaruInput').required = true;
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>