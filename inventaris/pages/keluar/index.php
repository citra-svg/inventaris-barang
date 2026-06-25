<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

$barang = get("SELECT * FROM barang ORDER BY nama_barang");

// PROSES TAMBAH BARANG KELUAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barang_id = $_POST['barang_id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $harga_jual = $_POST['harga_jual'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    if (empty($barang_id) || $jumlah <= 0 || $harga_jual <= 0) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        // Cek stok
        $cek_stok = getOne("SELECT stok FROM barang WHERE id = $barang_id");
        if ($cek_stok['stok'] < $jumlah) {
            $error = 'Stok tidak mencukupi! Stok tersedia: ' . $cek_stok['stok'];
        } else {
            $total_harga = $jumlah * $harga_jual;
            
            $sql = "INSERT INTO barang_keluar (barang_id, jumlah, harga_jual, total_harga, keterangan, user_id) 
                    VALUES ('$barang_id', '$jumlah', '$harga_jual', '$total_harga', '$keterangan', '$user_id')";
            
            if (insert($sql)) {
                // Kurangi stok
                $update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id = $barang_id";
                update($update_stok);
                
                $success = 'Barang keluar berhasil dicatat!';
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal mencatat barang keluar: ' . $conn->error;
            }
        }
    }
}

// PROSES HAPUS BARANG KELUAR
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    $data = getOne("SELECT * FROM barang_keluar WHERE id = $id");
    if ($data) {
        // Tambah stok kembali
        $update_stok = "UPDATE barang SET stok = stok + {$data['jumlah']} WHERE id = {$data['barang_id']}";
        update($update_stok);
        
        $sql = "DELETE FROM barang_keluar WHERE id = $id";
        if (delete($sql)) {
            echo "<script>
                alert('Riwayat barang keluar berhasil dihapus! Stok otomatis ditambahkan kembali.');
                window.location.href = 'index.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menghapus riwayat!');
                window.location.href = 'index.php';
            </script>";
        }
    } else {
        header("Location: index.php");
        exit();
    }
}

// AMBIL RIWAYAT BARANG KELUAR
$riwayat = get("
    SELECT bk.*, b.nama_barang, b.kode_barang, u.nama_lengkap 
    FROM barang_keluar bk 
    JOIN barang b ON bk.barang_id = b.id 
    JOIN users u ON bk.user_id = u.id 
    ORDER BY bk.tanggal_keluar DESC 
    LIMIT 100
");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-arrow-up-circle"></i> Barang Keluar</h4>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Input Barang Keluar
            </div>
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
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo htmlspecialchars($item['kode_barang']); ?> - <?php echo htmlspecialchars($item['nama_barang']); ?> (Stok: <?php echo $item['stok']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" placeholder="Jumlah barang keluar" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga Jual per Unit <span class="text-danger">*</span></label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="Harga jual per unit" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Keterangan (opsional)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-7 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Barang Keluar
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>User</th>
                                <th>Tanggal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($riwayat) > 0): ?>
                                <?php $no = 1; foreach ($riwayat as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                                        <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                        <td><span class="badge bg-warning text-dark"><?php echo $item['jumlah']; ?></span></td>
                                        <td>Rp <?php echo number_format($item['total_harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($item['nama_lengkap']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($item['tanggal_keluar'])); ?></td>
                                        <td class="text-center">
                                            <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?hapus=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus riwayat ini? Stok akan otomatis ditambahkan kembali.')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada riwayat barang keluar</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>