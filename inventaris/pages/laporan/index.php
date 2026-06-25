<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');

$laporan = get("
    SELECT 
        b.kode_barang,
        b.nama_barang,
        k.nama_kategori,
        b.harga,
        b.stok,
        b.min_stok,
        (SELECT SUM(jumlah) FROM barang_masuk WHERE barang_id = b.id AND tanggal_masuk BETWEEN '$tanggal_awal' AND '$tanggal_akhir 23:59:59') as total_masuk,
        (SELECT SUM(jumlah) FROM barang_keluar WHERE barang_id = b.id AND tanggal_keluar BETWEEN '$tanggal_awal' AND '$tanggal_akhir 23:59:59') as total_keluar
    FROM barang b
    LEFT JOIN kategori k ON b.kategori_id = k.id
    ORDER BY b.nama_barang
");

$total_masuk_all = getOne("SELECT SUM(jumlah) as total FROM barang_masuk WHERE tanggal_masuk BETWEEN '$tanggal_awal' AND '$tanggal_akhir 23:59:59'")['total'] ?? 0;
$total_keluar_all = getOne("SELECT SUM(jumlah) as total FROM barang_keluar WHERE tanggal_keluar BETWEEN '$tanggal_awal' AND '$tanggal_akhir 23:59:59'")['total'] ?? 0;
$total_barang = count($laporan);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-file-text"></i> Laporan Barang</h4>
    <div>
        <button onclick="window.print()" class="btn btn-dark print-btn">
            <i class="bi bi-printer"></i> Cetak
        </button>
        <a href="export_excel.php?tanggal_awal=<?php echo $tanggal_awal; ?>&tanggal_akhir=<?php echo $tanggal_akhir; ?>" class="btn btn-success">
            <i class="bi bi-file-excel"></i> Export Excel
        </a>
    </div>
</div>

<div class="card mb-4 no-print">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control" value="<?php echo $tanggal_awal; ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control" value="<?php echo $tanggal_akhir; ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_barang; ?></div>
                    <div class="stat-label">Total Barang</div>
                </div>
                <div class="stat-icon"><i class="bi bi-box"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_masuk_all; ?></div>
                    <div class="stat-label">Total Barang Masuk</div>
                </div>
                <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_keluar_all; ?></div>
                    <div class="stat-label">Total Barang Keluar</div>
                </div>
                <div class="stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_masuk_all - $total_keluar_all; ?></div>
                    <div class="stat-label">Selisih Masuk - Keluar</div>
                </div>
                <div class="stat-icon"><i class="bi bi-calculator"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="tableLaporan">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Min Stok</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($laporan) > 0): ?>
                        <?php $no = 1; foreach ($laporan as $item): ?>
                            <?php 
                            $masuk = $item['total_masuk'] ?? 0;
                            $keluar = $item['total_keluar'] ?? 0;
                            $status = $item['stok'] <= $item['min_stok'] ? 'Stok Menipis' : 'Aman';
                            $status_class = $item['stok'] <= $item['min_stok'] ? 'danger' : 'success';
                            ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['kode_barang']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?php echo htmlspecialchars($item['nama_kategori'] ?? '-'); ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo $item['stok']; ?></span></td>
                                <td><?php echo $item['min_stok']; ?></td>
                                <td><span class="badge bg-success"><?php echo $masuk; ?></span></td>
                                <td><span class="badge bg-warning text-dark"><?php echo $keluar; ?></span></td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">Tidak ada data barang</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 text-muted small">
            <i class="bi bi-info-circle"></i> Periode: <?php echo date('d/m/Y', strtotime($tanggal_awal)); ?> - <?php echo date('d/m/Y', strtotime($tanggal_akhir)); ?>
        </div>
    </div>
</div>

<!-- CSS untuk Print -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    .navbar {
        display: none !important;
    }
    .btn {
        display: none !important;
    }
    .stat-card {
        background: #f8f9fa !important;
        color: #333 !important;
        border: 1px solid #ddd !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .badge {
        border: 1px solid #ddd !important;
    }
    .table {
        font-size: 12px !important;
    }
    body {
        padding: 20px !important;
        background: white !important;
    }
    .container {
        max-width: 100% !important;
    }
}
</style>

<?php require_once '../../includes/footer.php'; ?>