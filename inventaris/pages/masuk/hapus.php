<?php
require_once '../../config/database.php';

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

// Ambil nama barang
$barang = getOne("SELECT nama_barang FROM barang WHERE id = {$data['barang_id']}");

// Proses hapus
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    // Kurangi stok barang (karena riwayat dihapus)
    $update_stok = "UPDATE barang SET stok = stok - {$data['jumlah']} WHERE id = {$data['barang_id']}";
    update($update_stok);
    
    // Hapus riwayat
    $sql = "DELETE FROM barang_masuk WHERE id = $id";
    if (delete($sql)) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Berhasil Hapus</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body>
            <div class='container mt-5'>
                <div class='alert alert-success'>
                    <h4><i class='bi bi-check-circle'></i> Berhasil!</h4>
                    <p>Riwayat barang masuk berhasil dihapus!</p>
                    <p>Stok <strong>'{$barang['nama_barang']}'</strong> otomatis berkurang sebanyak <strong>{$data['jumlah']}</strong>.</p>
                    <a href='index.php' class='btn btn-primary'>Kembali ke Barang Masuk</a>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>";
    } else {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Gagal Hapus</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body>
            <div class='container mt-5'>
                <div class='alert alert-danger'>
                    <h4><i class='bi bi-exclamation-triangle'></i> Gagal!</h4>
                    <p>Terjadi kesalahan saat menghapus data.</p>
                    <a href='index.php' class='btn btn-primary'>Kembali</a>
                </div>
            </div>
            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
        </body>
        </html>";
    }
    exit();
}

// Tampilkan konfirmasi
?>
<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Hapus</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus</h4>
            </div>
            <div class="card-body">
                <h5>Apakah Anda yakin ingin menghapus riwayat ini?</h5>
                <table class="table">
                    <tr>
                        <th>Barang</th>
                        <td><?php echo htmlspecialchars($barang['nama_barang']); ?></td>
                    </tr>
                    <tr>
                        <th>Jumlah</th>
                        <td><?php echo $data['jumlah']; ?></td>
                    </tr>
                    <tr>
                        <th>Harga Beli</th>
                        <td>Rp <?php echo number_format($data['harga_beli'], 0, ',', '.'); ?></td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>Rp <?php echo number_format($data['total_harga'], 0, ',', '.'); ?></td>
                    </tr>
                </table>
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle"></i> Stok barang akan otomatis <strong>BERKURANG</strong> sebanyak <?php echo $data['jumlah']; ?>.
                </div>
                <a href="?id=<?php echo $id; ?>&confirm=yes" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Ya, Hapus!
                </a>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>