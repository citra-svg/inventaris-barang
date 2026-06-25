<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = $_POST['nama_kategori'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        // Cek duplikat
        $cek = getOne("SELECT id FROM kategori WHERE nama_kategori = '$nama_kategori'");
        if ($cek) {
            $error = 'Nama kategori sudah digunakan!';
        } else {
            $sql = "INSERT INTO kategori (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')";
            if (insert($sql)) {
                $success = 'Kategori berhasil ditambahkan!';
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal menambahkan kategori: ' . $conn->error;
            }
        }
    }

    // Setelah berhasil insert
if (insert($sql)) {
    logAktivitas($_SESSION['user_id'], 'Tambah Kategori', "Menambahkan kategori: $nama_kategori");
    // ...
}
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-plus-circle"></i> Tambah Kategori</h4>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Elektronik" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi kategori"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>