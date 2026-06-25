<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

// Ambil data pengembalian dengan JOIN
$pengembalian = get("
    SELECT pg.*, p.kode_peminjaman, b.nama_barang, b.kode_barang, 
           u.nama_lengkap as pengembali, pu.nama_lengkap as peminjam
    FROM pengembalian pg
    JOIN peminjaman p ON pg.peminjaman_id = p.id
    JOIN barang b ON p.barang_id = b.id
    LEFT JOIN users u ON pg.user_id = u.id
    JOIN users pu ON p.user_id = pu.id
    ORDER BY pg.tanggal_kembali DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-arrow-return-left"></i> Riwayat Pengembalian</h4>
    <?php if (isAdmin()): ?>
        <a href="tambah.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Kembalikan Barang
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Kode Pinjam</th>
                        <th>Barang</th>
                        <th>Peminjam</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pengembalian) > 0): ?>
                        <?php $no = 1; foreach ($pengembalian as $item): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($item['kode_peminjaman']) ?></span></td>
                                <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($item['peminjam']) ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($item['tanggal_kembali'])) ?></td>
                                <td>
                                    <?php if ($item['denda'] > 0): ?>
                                        <span class="badge bg-danger">Rp <?= number_format($item['denda'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Rp 0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $item['kondisi_barang'] == 'Baik' ? 'bg-success' : ($item['kondisi_barang'] == 'Rusak' ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= $item['kondisi_barang'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data pengembalian
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>