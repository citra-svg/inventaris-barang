<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

if (!isLoggedIn()) {
    header("Location: ../login.php");
    exit();
}

// Ambil data peminjaman
$peminjaman = get("
    SELECT p.*, b.nama_barang, b.kode_barang, u.nama_lengkap as peminjam
    FROM peminjaman p
    JOIN barang b ON p.barang_id = b.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.tanggal_pinjam DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-hand-index-thumb"></i> Riwayat Peminjaman</h4>
    <?php if (isAdmin()): ?>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Pinjam Barang
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
                        <th>Kode</th>
                        <th>Barang</th>
                        <th>Peminjam</th>
                        <th>Jumlah</th>
                        <th>Tgl Pinjam</th>
                        <th>Harus Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($peminjaman) > 0): ?>
                        <?php $no = 1; foreach ($peminjaman as $item): 
                            // Tentukan badge status
                            if ($item['status'] == 'Dikembalikan') {
                                $badge = 'bg-success';
                            } elseif ($item['status'] == 'Terlambat') {
                                $badge = 'bg-danger';
                            } else {
                                $badge = 'bg-warning text-dark';
                            }
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($item['kode_peminjaman']) ?></span></td>
                                <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                                <td><?= htmlspecialchars($item['peminjam']) ?></td>
                                <td><?= $item['jumlah'] ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($item['tanggal_pinjam'])) ?></td>
                                <td><?= date('d-m-Y', strtotime($item['tanggal_harus_kembali'])) ?></td>
                                <td><span class="badge <?= $badge ?>"><?= $item['status'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data peminjaman
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>