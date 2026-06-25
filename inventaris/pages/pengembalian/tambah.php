<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

// Ambil daftar peminjaman yang masih aktif (belum dikembalikan)
$peminjaman_list = get("
    SELECT p.*, b.nama_barang, b.kode_barang, u.nama_lengkap as peminjam 
    FROM peminjaman p 
    JOIN barang b ON p.barang_id = b.id 
    JOIN users u ON p.user_id = u.id 
    WHERE p.status IN ('Dipinjam', 'Terlambat')
    ORDER BY p.tanggal_pinjam DESC
");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $peminjaman_id = (int)$_POST['peminjaman_id'];
    $kondisi_barang = $_POST['kondisi_barang'] ?? 'Baik';
    $keterangan = $_POST['keterangan'] ?? '';
    
    if ($peminjaman_id <= 0) {
        $error = 'Silakan pilih peminjaman!';
    } else {
        // Ambil data peminjaman
        $pinjam = getOne("SELECT * FROM peminjaman WHERE id = $peminjaman_id");
        
        if (!$pinjam) {
            $error = 'Data peminjaman tidak ditemukan!';
        } else {
            // Hitung denda jika terlambat
            $denda = 0;
            $tgl_harus_kembali = strtotime($pinjam['tanggal_harus_kembali']);
            $hari_ini = time();
            
            if ($hari_ini > $tgl_harus_kembali) {
                $selisih_hari = ceil(($hari_ini - $tgl_harus_kembali) / (60 * 60 * 24));
                $denda = $selisih_hari * 5000; // Rp 5.000 per hari
            }
            
            // Insert ke tabel pengembalian
            $sql = "INSERT INTO pengembalian (peminjaman_id, denda, kondisi_barang, keterangan, user_id) 
                    VALUES ($peminjaman_id, $denda, '$kondisi_barang', '$keterangan', {$_SESSION['user_id']})";
            
            if (insert($sql)) {
                // Update status peminjaman menjadi Dikembalikan
                update("UPDATE peminjaman SET status = 'Dikembalikan' WHERE id = $peminjaman_id");
                
                // Kembalikan stok barang
                update("UPDATE barang SET stok = stok + {$pinjam['jumlah']} WHERE id = {$pinjam['barang_id']}");
                
                $msg = 'Pengembalian berhasil!';
                if ($denda > 0) {
                    $msg .= ' Denda: Rp ' . number_format($denda, 0, ',', '.');
                }
                $success = $msg;
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal menyimpan: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-arrow-return-left"></i> Kembalikan Barang</h4>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="bi bi-info-circle"></i> Form Pengembalian Barang
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Peminjaman <span class="text-danger">*</span></label>
                <select name="peminjaman_id" class="form-select" required>
                    <option value="">-- Pilih Peminjaman --</option>
                    <?php foreach ($peminjaman_list as $item): ?>
                        <option value="<?= $item['id'] ?>">
                            <?= htmlspecialchars($item['kode_peminjaman']) ?> 
                            - <?= htmlspecialchars($item['nama_barang']) ?> 
                            (<?= htmlspecialchars($item['peminjam']) ?>)
                            - Jumlah: <?= $item['jumlah'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (count($peminjaman_list) == 0): ?>
                    <small class="text-danger">Tidak ada peminjaman aktif yang bisa dikembalikan.</small>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Kondisi Barang <span class="text-danger">*</span></label>
                <select name="kondisi_barang" class="form-select" required>
                    <option value="Baik">Baik</option>
                    <option value="Rusak">Rusak</option>
                    <option value="Hilang">Hilang</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
            </div>
            
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> <strong>Denda otomatis:</strong> Rp 5.000 per hari keterlambatan
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Proses Pengembalian
                </button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>