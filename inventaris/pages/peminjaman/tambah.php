<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isAdmin()) {
    header("Location: index.php");
    exit();
}

// Ambil data barang yang stoknya > 0
$barang = get("SELECT * FROM barang WHERE stok > 0 ORDER BY nama_barang");

// Ambil data users
$users = get("SELECT * FROM users ORDER BY nama_lengkap");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barang_id = (int)$_POST['barang_id'];
    $user_id = (int)$_POST['user_id'];
    $jumlah = (int)$_POST['jumlah'];
    $tanggal_harus_kembali = $_POST['tanggal_harus_kembali'] ?? '';
    $keterangan = $_POST['keterangan'] ?? '';
    
    if ($barang_id <= 0 || $user_id <= 0 || $jumlah <= 0 || empty($tanggal_harus_kembali)) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Cek stok
        $cek_stok = getOne("SELECT stok, nama_barang FROM barang WHERE id = $barang_id");
        
        if ($jumlah > $cek_stok['stok']) {
            $error = 'Stok tidak mencukupi! Stok tersedia: ' . $cek_stok['stok'];
        } else {
            // Generate kode peminjaman
            $kode = 'PMJ-' . date('Ymd') . '-' . rand(100, 999);
            
            $sql = "INSERT INTO peminjaman (kode_peminjaman, barang_id, user_id, jumlah, tanggal_harus_kembali, keterangan) 
                    VALUES ('$kode', $barang_id, $user_id, $jumlah, '$tanggal_harus_kembali', '$keterangan')";
            
            if (insert($sql)) {
                // Kurangi stok
                update("UPDATE barang SET stok = stok - $jumlah WHERE id = $barang_id");
                
                $success = 'Peminjaman berhasil! Kode: ' . $kode;
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal menyimpan: ' . $conn->error;
            }
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-plus-circle"></i> Pinjam Barang</h4>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-info-circle"></i> Form Peminjaman Barang
    </div>
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
                    <label class="form-label fw-bold">Pilih Barang <span class="text-danger">*</span></label>
                    <select name="barang_id" class="form-select" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($barang as $item): ?>
                            <option value="<?= $item['id'] ?>">
                                <?= htmlspecialchars($item['kode_barang']) ?> - <?= htmlspecialchars($item['nama_barang']) ?>
                                (Stok: <?= $item['stok'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (count($barang) == 0): ?>
                        <small class="text-danger">Tidak ada barang yang tersedia untuk dipinjam.</small>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Peminjam <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Pilih Peminjam --</option>
                        <?php foreach ($users as $item): ?>
                            <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nama_lengkap']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" class="form-control" min="1" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Tanggal Harus Kembali <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_harus_kembali" class="form-control" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Catatan (opsional)">
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Peminjaman
                </button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
// Set default tanggal kembali = 7 hari dari sekarang
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="tanggal_harus_kembali"]');
    if (dateInput) {
        const today = new Date();
        const sevenDays = new Date(today);
        sevenDays.setDate(sevenDays.getDate() + 7);
        dateInput.value = sevenDays.toISOString().split('T')[0];
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>