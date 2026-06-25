<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
$kategori = getOne("SELECT * FROM kategori WHERE id = $id");

if (!$kategori) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = $_POST['nama_kategori'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    if (empty($nama_kategori)) {
        $error = 'Nama kategori wajib diisi!';
    } else {
        // Cek duplikat (kecuali dirinya sendiri)
        $cek = getOne("SELECT id FROM kategori WHERE nama_kategori = '$nama_kategori' AND id != $id");
        if ($cek) {
            $error = 'Nama kategori sudah digunakan!';
        } else {
            $sql = "UPDATE kategori SET nama_kategori = '$nama_kategori', deskripsi = '$deskripsi' WHERE id = $id";
            if (update($sql)) {
                $success = 'Kategori berhasil diupdate!';
                $kategori = getOne("SELECT * FROM kategori WHERE id = $id");
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal mengupdate kategori: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil"></i> Edit Kategori</h4>
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
                <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($kategori['nama_kategori']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($kategori['deskripsi']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update
            </button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>