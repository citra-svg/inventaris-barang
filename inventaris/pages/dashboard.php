<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';
require_once '../includes/header.php';

// Statistik
$total_barang = getOne("SELECT COUNT(*) as total FROM barang")['total'] ?? 0;
$total_kategori = getOne("SELECT COUNT(*) as total FROM kategori")['total'] ?? 0;
$total_masuk = getOne("SELECT SUM(jumlah) as total FROM barang_masuk")['total'] ?? 0;
$total_keluar = getOne("SELECT SUM(jumlah) as total FROM barang_keluar")['total'] ?? 0;

// Barang dengan stok menipis
$barang_menipis = get("SELECT * FROM barang WHERE stok <= min_stok ORDER BY stok ASC LIMIT 5");

// Transaksi terbaru
$transaksi_terbaru = get("
    SELECT 'masuk' as jenis, b.nama_barang, bm.jumlah, bm.tanggal_masuk as tanggal, u.nama_lengkap 
    FROM barang_masuk bm 
    JOIN barang b ON bm.barang_id = b.id 
    JOIN users u ON bm.user_id = u.id 
    ORDER BY bm.tanggal_masuk DESC LIMIT 5
");

$transaksi_keluar = get("
    SELECT 'keluar' as jenis, b.nama_barang, bk.jumlah, bk.tanggal_keluar as tanggal, u.nama_lengkap 
    FROM barang_keluar bk 
    JOIN barang b ON bk.barang_id = b.id 
    JOIN users u ON bk.user_id = u.id 
    ORDER BY bk.tanggal_keluar DESC LIMIT 5
");

$transaksi = array_merge($transaksi_terbaru, $transaksi_keluar);
usort($transaksi, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});
$transaksi = array_slice($transaksi, 0, 5);

// Data untuk chart
$stok_data = get("SELECT nama_barang, stok FROM barang ORDER BY stok DESC LIMIT 10");
$labels_stok = [];
$values_stok = [];
foreach ($stok_data as $item) {
    $labels_stok[] = $item['nama_barang'];
    $values_stok[] = $item['stok'];
}

$kategori_data = get("
    SELECT k.nama_kategori, COUNT(b.id) as total 
    FROM kategori k 
    LEFT JOIN barang b ON k.id = b.kategori_id 
    GROUP BY k.id
");
$labels_kategori = [];
$values_kategori = [];
foreach ($kategori_data as $item) {
    $labels_kategori[] = $item['nama_kategori'];
    $values_kategori[] = $item['total'];
}
?>

<!-- ============ STATISTIK CARDS ============ -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3 animate-in">
        <div class="stat-card stat-card-gradient-1">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_barang; ?></div>
                    <div class="stat-label">Total Barang</div>
                </div>
                <div class="stat-icon"><i class="bi bi-box-fill"></i></div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min($total_barang, 100); ?>%;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3 animate-in animate-in-delay-1">
        <div class="stat-card stat-card-gradient-2">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_kategori; ?></div>
                    <div class="stat-label">Total Kategori</div>
                </div>
                <div class="stat-icon"><i class="bi bi-tags-fill"></i></div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min($total_kategori * 10, 100); ?>%;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3 animate-in animate-in-delay-2">
        <div class="stat-card stat-card-gradient-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_masuk; ?></div>
                    <div class="stat-label">Barang Masuk</div>
                </div>
                <div class="stat-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min($total_masuk, 100); ?>%;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3 animate-in animate-in-delay-3">
        <div class="stat-card stat-card-gradient-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number"><?php echo $total_keluar; ?></div>
                    <div class="stat-label">Barang Keluar</div>
                </div>
                <div class="stat-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
            </div>
            <div class="stat-progress">
                <div class="stat-progress-bar" style="width: <?php echo min($total_keluar, 100); ?>%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ CHART & AKTIVITAS ============ -->
<div class="row">
    <div class="col-lg-8 mb-4 animate-in animate-in-delay-1">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart-fill me-2" style="color: #667eea;"></i> 
                Grafik Stok Barang
            </div>
            <div class="card-body">
                <canvas id="stokChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4 animate-in animate-in-delay-2">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart-fill me-2" style="color: #f093fb;"></i> 
                Distribusi Kategori
            </div>
            <div class="card-body">
                <canvas id="kategoriChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ============ STOK MENIPIS & AKTIVITAS ============ -->
<div class="row">
    <div class="col-lg-6 mb-4 animate-in animate-in-delay-2">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle-fill me-2" style="color: #f5576c;"></i> 
                Barang Stok Menipis
                <?php if (count($barang_menipis) > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo count($barang_menipis); ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (count($barang_menipis) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Stok</th>
                                    <th>Min Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barang_menipis as $item): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['kode_barang']); ?></span></td>
                                        <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                        <td><span class="badge bg-danger"><?php echo $item['stok']; ?></span></td>
                                        <td><?php echo $item['min_stok']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                        Semua stok barang aman
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4 animate-in animate-in-delay-3">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2" style="color: #4facfe;"></i> 
                Aktivitas Terbaru
            </div>
            <div class="card-body">
                <?php if (count($transaksi) > 0): ?>
                    <div class="activity-timeline">
                        <?php foreach ($transaksi as $item): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $item['jenis'] == 'masuk' ? 'activity-icon-success' : 'activity-icon-warning'; ?>">
                                    <i class="bi bi-<?php echo $item['jenis'] == 'masuk' ? 'arrow-down' : 'arrow-up'; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <strong><?php echo htmlspecialchars($item['nama_barang']); ?></strong>
                                        <span class="badge <?php echo $item['jenis'] == 'masuk' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst($item['jenis']); ?>
                                        </span>
                                    </div>
                                    <div class="activity-meta">
                                        <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($item['nama_lengkap']); ?></span>
                                        <span><i class="bi bi-box"></i> <?php echo $item['jumlah']; ?></span>
                                        <span><i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($item['tanggal'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        Belum ada aktivitas
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============ CHART JS ============ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Stok Barang
    var ctx1 = document.getElementById('stokChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_stok); ?>,
            datasets: [{
                label: 'Jumlah Stok',
                data: <?php echo json_encode($values_stok); ?>,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.7)',
                    'rgba(79, 172, 254, 0.7)',
                    'rgba(67, 233, 123, 0.7)',
                    'rgba(245, 87, 108, 0.7)',
                    'rgba(240, 147, 251, 0.7)',
                    'rgba(56, 249, 215, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(13, 202, 240, 0.7)',
                    'rgba(255, 107, 107, 0.7)',
                    'rgba(54, 194, 174, 0.7)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(79, 172, 254, 1)',
                    'rgba(67, 233, 123, 1)',
                    'rgba(245, 87, 108, 1)',
                    'rgba(240, 147, 251, 1)',
                    'rgba(56, 249, 215, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(13, 202, 240, 1)',
                    'rgba(255, 107, 107, 1)',
                    'rgba(54, 194, 174, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Grafik Distribusi Kategori
    var ctx2 = document.getElementById('kategoriChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels_kategori); ?>,
            datasets: [{
                data: <?php echo json_encode($values_kategori); ?>,
                backgroundColor: [
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(245, 87, 108, 0.8)',
                    'rgba(67, 233, 123, 0.8)',
                    'rgba(240, 147, 251, 0.8)',
                    'rgba(79, 172, 254, 0.8)',
                    'rgba(56, 249, 215, 0.8)'
                ],
                borderColor: [
                    'rgba(102, 126, 234, 1)',
                    'rgba(245, 87, 108, 1)',
                    'rgba(67, 233, 123, 1)',
                    'rgba(240, 147, 251, 1)',
                    'rgba(79, 172, 254, 1)',
                    'rgba(56, 249, 215, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>