<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter
$filter_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$filter_stok = isset($_GET['stok']) ? $_GET['stok'] : '';

// Query dengan filter
$where = "1=1";
if (!empty($filter_kategori)) {
    $where .= " AND k.nama_kategori = '$filter_kategori'";
}
if ($filter_stok == 'menipis') {
    $where .= " AND b.stok <= b.min_stok AND b.stok > 0";
} elseif ($filter_stok == 'habis') {
    $where .= " AND b.stok = 0";
} elseif ($filter_stok == 'aman') {
    $where .= " AND b.stok > b.min_stok";
}

$total_data = getOne("
    SELECT COUNT(*) as total 
    FROM barang b 
    LEFT JOIN kategori k ON b.kategori_id = k.id 
    WHERE $where
")['total'] ?? 0;
$total_pages = ceil($total_data / $limit);

$barang = get("
    SELECT b.*, k.nama_kategori 
    FROM barang b 
    LEFT JOIN kategori k ON b.kategori_id = k.id 
    WHERE $where
    ORDER BY b.id DESC 
    LIMIT $limit OFFSET $offset
");

// Ambil semua kategori untuk filter
$semua_kategori = get("SELECT * FROM kategori ORDER BY nama_kategori");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-box"></i> Data Barang</h4>
    <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Barang
    </a>
</div>

<div class="card">
    <div class="card-body">
        <!-- FILTER & PENCARIAN -->
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="filterBarang" class="form-control" placeholder="Cari barang..." onkeyup="filterTable('filterBarang', 'tableBarang')">
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('filterBarang').value='';filterTable('filterBarang','tableBarang')">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select id="filterKategori" class="form-select" onchange="filterByKategori()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($semua_kategori as $kat): ?>
                        <option value="<?php echo htmlspecialchars($kat['nama_kategori']); ?>" <?php echo $filter_kategori == $kat['nama_kategori'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStok" class="form-select" onchange="filterByStok()">
                    <option value="">Semua Stok</option>
                    <option value="menipis" <?php echo $filter_stok == 'menipis' ? 'selected' : ''; ?>>Stok Menipis</option>
                    <option value="aman" <?php echo $filter_stok == 'aman' ? 'selected' : ''; ?>>Stok Aman</option>
                    <option value="habis" <?php echo $filter_stok == 'habis' ? 'selected' : ''; ?>>Stok Habis</option>
                </select>
            </div>
        </div>

        <!-- TABEL BARANG -->
        <div class="table-responsive">
            <table class="table table-hover" id="tableBarang">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Min Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($barang) > 0): ?>
                        <?php $no = $offset + 1; foreach ($barang as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($item['kode_barang']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?php echo htmlspecialchars($item['nama_kategori'] ?? '-'); ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php echo $item['stok'] <= $item['min_stok'] ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo $item['stok']; ?>
                                    </span>
                                </td>
                                <td><?php echo $item['min_stok']; ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="hapus.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data barang
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&kategori=<?php echo $filter_kategori; ?>&stok=<?php echo $filter_stok; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&kategori=<?php echo $filter_kategori; ?>&stok=<?php echo $filter_stok; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&kategori=<?php echo $filter_kategori; ?>&stok=<?php echo $filter_stok; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
        
        <div class="text-muted small mt-2">
            Menampilkan <?php echo count($barang); ?> dari <?php echo $total_data; ?> data
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>