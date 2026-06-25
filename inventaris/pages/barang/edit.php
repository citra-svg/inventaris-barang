<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
$barang = getOne("SELECT * FROM barang WHERE id = $id");

if (!$barang) {
    header("Location: index.php");
    exit();
}

$kategori = get("SELECT * FROM kategori ORDER BY nama_kategori");
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_barang = $_POST['kode_barang'] ?? '';
    $nama_barang = $_POST['nama_barang'] ?? '';
    $kategori_id = $_POST['kategori_id'] ?? '';
    $harga = $_POST['harga'] ?? 0;
    $stok = $_POST['stok'] ?? 0;
    $min_stok = $_POST['min_stok'] ?? 5;
    $deskripsi = $_POST['deskripsi'] ?? '';
    
    if (empty($kode_barang) || empty($nama_barang) || empty($kategori_id) || $harga <= 0) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Cek kode barang duplikat (kecuali dirinya sendiri)
        $cek = getOne("SELECT id FROM barang WHERE kode_barang = '$kode_barang' AND id != $id");
        if ($cek) {
            $error = 'Kode barang sudah digunakan!';
        } else {
            $sql = "UPDATE barang SET 
                    kode_barang = '$kode_barang',
                    nama_barang = '$nama_barang',
                    kategori_id = '$kategori_id',
                    harga = '$harga',
                    stok = '$stok',
                    min_stok = '$min_stok',
                    deskripsi = '$deskripsi'
                    WHERE id = $id";
            
            if (update($sql)) {
                $success = 'Barang berhasil diupdate!';
                $barang = getOne("SELECT * FROM barang WHERE id = $id");
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal mengupdate barang: ' . $conn->error;
            }

            // Setelah berhasil update
if (update($sql)) {
    logAktivitas($_SESSION['user_id'], 'Edit Barang', "Mengedit barang: $nama_barang (Kode: $kode_barang)");
    $success = 'Barang berhasil diupdate!';
    // ...
}
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil"></i> Edit Barang</h4>
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
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Kode Barang <span class="text-danger">*</span></label>
                    <input type="text" name="kode_barang" class="form-control" value="<?= htmlspecialchars($barang['kode_barang']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($barang['nama_barang']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori_id" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategori as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $barang['kategori_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Harga <span class="text-danger">*</span></label>
                    <input type="number" name="harga" class="form-control" value="<?= $barang['harga'] ?>" min="0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?= $barang['stok'] ?>" min="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Minimum Stok</label>
                    <input type="number" name="min_stok" class="form-control" value="<?= $barang['min_stok'] ?>" min="0">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($barang['deskripsi']) ?></textarea>
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

<?php require_once '../../includes/footer.php'; ?>