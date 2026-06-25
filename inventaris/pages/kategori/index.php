<?php
require_once '../../config/database.php';
require_once '../../includes/header.php';

$kategori = get("SELECT * FROM kategori ORDER BY id DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-tags"></i> Data Kategori</h4>
    <a href="tambah.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Kategori
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($kategori) > 0): ?>
                        <?php $no = 1; foreach ($kategori as $item): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($item['nama_kategori']) ?></strong></td>
                                <td><?= htmlspecialchars($item['deskripsi'] ?? '-') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="confirmDelete('hapus.php?id=<?= $item['id'] ?>', '<?= $item['nama_kategori'] ?>')" class="btn btn-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data kategori</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>