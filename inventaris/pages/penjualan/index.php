<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$error = '';
$success = '';

$barang = get("SELECT * FROM barang ORDER BY nama_barang");

$transaksi = get("
    SELECT t.*, u.nama_lengkap 
    FROM transaksi t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC 
    LIMIT 50
");

// PROSES TAMBAH TRANSAKSI
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'tambah') {
    $barang_id = $_POST['barang_id'] ?? '';
    $jumlah = $_POST['jumlah'] ?? 0;
    $harga = $_POST['harga'] ?? 0;
    $user_id = $_SESSION['user_id'];
    
    if (empty($barang_id) || $jumlah <= 0 || $harga <= 0) {
        $error = 'Semua field wajib diisi dengan benar!';
    } else {
        $cek_stok = getOne("SELECT stok, nama_barang FROM barang WHERE id = $barang_id");
        if ($cek_stok['stok'] < $jumlah) {
            $error = 'Stok tidak mencukupi! Stok tersedia: ' . $cek_stok['stok'];
        } else {
            $subtotal = $jumlah * $harga;
            $kode_transaksi = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999);
            
            $sql = "INSERT INTO transaksi (kode_transaksi, user_id, total_harga, status) 
                    VALUES ('$kode_transaksi', '$user_id', '$subtotal', 'pending')";
            
            $transaksi_id = insert($sql);
            
            if ($transaksi_id) {
                $sql_detail = "INSERT INTO transaksi_detail (transaksi_id, barang_id, jumlah, harga, subtotal) 
                               VALUES ('$transaksi_id', '$barang_id', '$jumlah', '$harga', '$subtotal')";
                insert($sql_detail);
                
                $qr_string = "PAY-" . $kode_transaksi . "-" . $subtotal;
                $expired_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $sql_qr = "INSERT INTO qr_payment (transaksi_id, qr_string, expired_at) 
                           VALUES ('$transaksi_id', '$qr_string', '$expired_at')";
                insert($sql_qr);
                
                $update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id = $barang_id";
                update($update_stok);
                
                logAktivitas($user_id, 'Transaksi Baru', "Transaksi: $kode_transaksi - Total: Rp " . number_format($subtotal, 0, ',', '.'));
                
                $success = 'Transaksi berhasil dibuat! Kode: ' . $kode_transaksi;
                echo "<script>setTimeout(() => window.location.href='index.php', 1500);</script>";
            } else {
                $error = 'Gagal membuat transaksi: ' . $conn->error;
            }
        }
    }
}

// PROSES KONFIRMASI PEMBAYARAN
if (isset($_GET['bayar'])) {
    $id = (int)$_GET['bayar'];
    
    $sql = "UPDATE transaksi SET status = 'paid', paid_at = NOW() WHERE id = $id";
    if (update($sql)) {
        $sql_qr = "UPDATE qr_payment SET status = 'used' WHERE transaksi_id = $id";
        update($sql_qr);
        
        logAktivitas($_SESSION['user_id'], 'Pembayaran Sukses', "Transaksi ID: $id");
        echo "<script>
            alert('Pembayaran berhasil dikonfirmasi! Status: Lunas');
            window.location.href = 'index.php';
        </script>";
    }
}

// PROSES HAPUS TRANSAKSI (PENDING)
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $trans = getOne("SELECT * FROM transaksi WHERE id = $id");
    if ($trans && $trans['status'] == 'pending') {
        $details = get("SELECT * FROM transaksi_detail WHERE transaksi_id = $id");
        foreach ($details as $detail) {
            $update_stok = "UPDATE barang SET stok = stok + {$detail['jumlah']} WHERE id = {$detail['barang_id']}";
            update($update_stok);
        }
        
        $sql = "DELETE FROM transaksi WHERE id = $id";
        if (delete($sql)) {
            logAktivitas($_SESSION['user_id'], 'Hapus Transaksi', "Menghapus transaksi ID: $id");
            echo "<script>
                alert('Transaksi berhasil dihapus!');
                window.location.href = 'index.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Transaksi sudah lunas, tidak bisa dihapus!');
            window.location.href = 'index.php';
        </script>";
    }
}

// ============================================
// PROSES HAPUS TRANSAKSI (SEMUA STATUS - KHUSUS ADMIN)
// ============================================
if (isset($_GET['hapus_semua']) && $_SESSION['role'] == 'admin') {
    $id = (int)$_GET['hapus_semua'];
    $trans = getOne("SELECT * FROM transaksi WHERE id = $id");
    
    if ($trans) {
        // Jika status pending, kembalikan stok
        if ($trans['status'] == 'pending') {
            $details = get("SELECT * FROM transaksi_detail WHERE transaksi_id = $id");
            foreach ($details as $detail) {
                $update_stok = "UPDATE barang SET stok = stok + {$detail['jumlah']} WHERE id = {$detail['barang_id']}";
                update($update_stok);
            }
        }
        
        // Hapus transaksi
        $sql = "DELETE FROM transaksi WHERE id = $id";
        if (delete($sql)) {
            logAktivitas($_SESSION['user_id'], 'Hapus Transaksi (Admin)', "Menghapus transaksi ID: $id - Status: {$trans['status']}");
            echo "<script>
                alert('Transaksi berhasil dihapus oleh Admin!');
                window.location.href = 'index.php';
            </script>";
        }
    }
}

// PROSES HAPUS SEMUA RIWAYAT (KHUSUS ADMIN)
if (isset($_GET['hapus_all']) && $_SESSION['role'] == 'admin') {
    // Ambil semua transaksi pending untuk mengembalikan stok
    $pending = get("SELECT id FROM transaksi WHERE status = 'pending'");
    foreach ($pending as $p) {
        $details = get("SELECT * FROM transaksi_detail WHERE transaksi_id = {$p['id']}");
        foreach ($details as $detail) {
            $update_stok = "UPDATE barang SET stok = stok + {$detail['jumlah']} WHERE id = {$detail['barang_id']}";
            update($update_stok);
        }
    }
    
    $sql = "DELETE FROM transaksi";
    if (delete($sql)) {
        logAktivitas($_SESSION['user_id'], 'Hapus Semua Transaksi', 'Menghapus semua riwayat transaksi');
        echo "<script>
            alert('Semua riwayat transaksi berhasil dihapus!');
            window.location.href = 'index.php';
        </script>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-cart"></i> Transaksi Penjualan</h4>
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <a href="?hapus_all=yes" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus SEMUA riwayat transaksi?')">
            <i class="bi bi-trash"></i> Hapus Semua
        </a>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-5 mb-4 animate-in">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Buat Transaksi Baru
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="tambah">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Barang <span class="text-danger">*</span></label>
                        <select name="barang_id" class="form-select" required>
                            <option value="">Pilih Barang</option>
                            <?php foreach ($barang as $item): ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo htmlspecialchars($item['kode_barang']); ?> - <?php echo htmlspecialchars($item['nama_barang']); ?> (Stok: <?php echo $item['stok']; ?>, Harga: Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah" class="form-control" placeholder="Jumlah barang" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Harga Jual per Unit <span class="text-danger">*</span></label>
                        <input type="number" name="harga" class="form-control" placeholder="Harga jual per unit" min="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cart-plus"></i> Buat Transaksi
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-7 mb-4 animate-in animate-in-delay-1">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Riwayat Transaksi
                <?php if (count($transaksi) > 0): ?>
                    <span class="badge bg-secondary ms-2"><?php echo count($transaksi); ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($transaksi) > 0): ?>
                                <?php $no = 1; foreach ($transaksi as $item): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['kode_transaksi']); ?></span></td>
                                        <td>Rp <?php echo number_format($item['total_harga'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php 
                                            $status_label = getStatusLabel($item['status']);
                                            $status_badge = getStatusBadge($item['status']);
                                            ?>
                                            <span class="badge <?php echo $status_badge; ?>">
                                                <?php echo $status_label; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($item['created_at'])); ?></td>
                                        <td>
                                            <?php if ($item['status'] == 'pending'): ?>
                                                <a href="qr.php?id=<?php echo $item['id']; ?>" class="btn btn-info btn-sm" title="Lihat QR">
                                                    <i class="bi bi-qr-code"></i>
                                                </a>
                                                <a href="?bayar=<?php echo $item['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi pembayaran?')" title="Konfirmasi Bayar">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                                <a href="?hapus=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus transaksi ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <!-- Transaksi Lunas - Hanya Admin Bisa Hapus -->
                                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                                    <a href="?hapus_semua=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus transaksi lunas ini?')" title="Hapus (Admin)">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled title="Sudah Lunas">
                                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada transaksi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($_SESSION['role'] == 'admin' && count($transaksi) > 0): ?>
                    <div class="text-end mt-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Admin dapat menghapus semua riwayat termasuk yang sudah lunas
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>