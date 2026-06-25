<?php
require_once '../../config/database.php';

$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="Laporan_Inventaris_' . date('Y-m-d') . '.xls"');

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
?>

<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Min Stok</th>
            <th>Barang Masuk</th>
            <th>Barang Keluar</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($laporan as $item): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $item['kode_barang']; ?></td>
                <td><?php echo $item['nama_barang']; ?></td>
                <td><?php echo $item['nama_kategori'] ?? '-'; ?></td>
                <td><?php echo $item['harga']; ?></td>
                <td><?php echo $item['stok']; ?></td>
                <td><?php echo $item['min_stok']; ?></td>
                <td><?php echo $item['total_masuk'] ?? 0; ?></td>
                <td><?php echo $item['total_keluar'] ?? 0; ?></td>
                <td><?php echo $item['stok'] <= $item['min_stok'] ? 'Stok Menipis' : 'Aman'; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>