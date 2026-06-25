<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

// Ambil data kategori untuk dropdown
$kategori = get("SELECT * FROM kategori ORDER BY nama_kategori");

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
        // Cek kode barang duplikat
        $cek = getOne("SELECT id FROM barang WHERE kode_barang = '$kode_barang'");
        if ($cek) {
            $error = 'Kode barang sudah digunakan!';
        } else {
            $sql = "INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga, stok, min_stok, deskripsi) 
                    VALUES ('$kode_barang', '$nama_barang', '$kategori_id', '$harga', '$stok', '$min_stok', '$deskripsi')";
            
            if (insert($sql)) {
                $success = 'Barang berhasil ditambahkan!';
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal menambahkan barang: ' . $conn->error;
            }
        }
    }

    // Setelah berhasil insert
    if (insert($sql)) {
    logAktivitas($_SESSION['user_id'], 'Tambah Barang', "Menambahkan barang: $nama_barang (Kode: $kode_barang)");
    $success = 'Barang berhasil ditambahkan!';
    // ...
}
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-plus-circle"></i> Tambah Barang</h4>
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
                    <input type="text" name="kode_barang" class="form-control" placeholder="Contoh: BRG-001" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" name="nama_barang" class="form-control" placeholder="Nama barang" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori_id" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategori as $kat): ?>
                            <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Harga <span class="text-danger">*</span></label>
                    <input type="number" name="harga" class="form-control" placeholder="0" min="0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Stok Awal</label>
                    <input type="number" name="stok" class="form-control" placeholder="0" min="0" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Minimum Stok</label>
                    <input type="number" name="min_stok" class="form-control" placeholder="5" min="0" value="5">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi barang"></textarea>
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


<?php require_once '../../includes/footer.php'; ?>