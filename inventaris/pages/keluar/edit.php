<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$data = getOne("SELECT * FROM barang_keluar WHERE id = $id");
if (!$data) {
    header("Location: index.php");
    exit();
}

$barang = get("SELECT * FROM barang ORDER BY nama_barang");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barang_id = $_POST['barang_id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';
    
    if (empty($barang_id) || $jumlah <= 0 || $harga_jual <= 0) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Cek stok
        $cek_stok = getOne("SELECT stok FROM barang WHERE id = $barang_id");
        $selisih = $jumlah - $data['jumlah'];
        
        if ($selisih > 0 && $cek_stok['stok'] < $selisih) {
            $error = 'Stok tidak mencukupi! Stok tersedia: ' . $cek_stok['stok'];
        } else {
            $total_harga = $jumlah * $harga_jual;
            
            $sql = "UPDATE barang_keluar SET 
                    barang_id = '$barang_id',
                    jumlah = '$jumlah',
                    harga_jual = '$harga_jual',
                    total_harga = '$total_harga',
                    keterangan = '$keterangan'
                    WHERE id = $id";
            
            if (update($sql)) {
                // Update stok (selisih negatif = stok bertambah, selisih positif = stok berkurang)
                $update_stok = "UPDATE barang SET stok = stok - $selisih WHERE id = $barang_id";
                update($update_stok);
                
                $success = 'Data barang keluar berhasil diupdate!';
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal mengupdate data: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-pencil"></i> Edit Barang Keluar</h4>
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
                <label class="form-label fw-semibold">Harga Jual per Unit <span class="text-danger">*</span></label>
                <input type="number" name="harga_jual" class="form-control" value="<?php echo $data['harga_jual']; ?>" min="1" required>
                <small class="text-muted">Harga sebelumnya: Rp <?php echo number_format($data['harga_jual'], 0, ',', '.'); ?></small>
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