<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Ambil data barang masuk
$data = getOne("SELECT * FROM barang_masuk WHERE id = $id");
if (!$data) {
    header("Location: index.php");
    exit();
}

// Ambil data barang untuk dropdown
$barang = get("SELECT * FROM barang ORDER BY nama_barang");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barang_id = $_POST['barang_id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $harga_beli = $_POST['harga_beli'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';
    
    if (empty($barang_id) || $jumlah <= 0 || $harga_beli <= 0) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Hitung selisih jumlah untuk update stok
        $selisih = $jumlah - $data['jumlah'];
        $total_harga = $jumlah * $harga_beli;
        
        // Update tabel barang_masuk
        $sql = "UPDATE barang_masuk SET 
                barang_id = '$barang_id',
                jumlah = '$jumlah',
                harga_beli = '$harga_beli',
                total_harga = '$total_harga',
                keterangan = '$keterangan'
                WHERE id = $id";
        
        if (update($sql)) {
            // Update stok barang
            $update_stok = "UPDATE barang SET stok = stok + $selisih WHERE id = $barang_id";
            update($update_stok);
            
            $success = 'Data barang masuk berhasil diupdate!';
            echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
        } else {
            $error = 'Gagal mengupdate data: ' . $conn->error;
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil"></i> Edit Barang Masuk</h4>
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
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">Pilih Barang <span class="text-danger">*</span></label>
                <select name="barang_id" class="form-select" required>
                    <option value="">Pilih Barang</option>
                    <?php foreach ($barang as $item): ?>
                        <option value="<?php echo $item['id']; ?>" <?php echo $item['id'] == $data['barang_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['kode_barang']); ?> - <?php echo htmlspecialchars($item['nama_barang']); ?> (Stok: <?php echo $item['stok']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Perubahan stok akan otomatis menyesuaikan</small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                <input type="number" name="jumlah" class="form-control" value="<?php echo $data['jumlah']; ?>" min="1" required>
                <small class="text-muted">Jumlah sebelumnya: <?php echo $data['jumlah']; ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Harga Beli per Unit <span class="text-danger">*</span></label>
                <input type="number" name="harga_beli" class="form-control" value="<?php echo $data['harga_beli']; ?>" min="1" required>
                <small class="text-muted">Harga sebelumnya: Rp <?php echo number_format($data['harga_beli'], 0, ',', '.'); ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2"><?php echo htmlspecialchars($data['keterangan']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update
            </button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>