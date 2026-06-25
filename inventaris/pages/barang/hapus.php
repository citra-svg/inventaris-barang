<?php
require_once '../../config/database.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Cek data barang
$barang = getOne("SELECT * FROM barang WHERE id = $id");

if (!$barang) {
    header("Location: index.php");
    exit();
}

// Cek apakah barang memiliki transaksi
$cek_masuk = getOne("SELECT id FROM barang_masuk WHERE barang_id = $id LIMIT 1");
$cek_keluar = getOne("SELECT id FROM barang_keluar WHERE barang_id = $id LIMIT 1");

if ($cek_masuk || $cek_keluar) {
    // Jika ada transaksi, tidak bisa dihapus
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Gagal Hapus</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-danger'>
                <h4><i class='bi bi-exclamation-triangle'></i> Gagal Menghapus!</h4>
                <p>Barang <strong>'{$barang['nama_barang']}'</strong> tidak bisa dihapus karena sudah memiliki riwayat transaksi (barang masuk atau barang keluar).</p>
                <a href='index.php' class='btn btn-primary'>Kembali</a>
            </div>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit();
}

// Jika tidak ada transaksi, hapus barang
$sql = "DELETE FROM barang WHERE id = $id";

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
                <p>Barang <strong>'{$barang['nama_barang']}'</strong> berhasil dihapus.</p>
                <a href='index.php' class='btn btn-primary'>Kembali</a>
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
                <h4><i class='bi bi-exclamation-triangle'></i> Gagal Menghapus!</h4>
                <p>Terjadi kesalahan saat menghapus data.</p>
                <a href='index.php' class='btn btn-primary'>Kembali</a>
            </div>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
}

// Setelah berhasil hapus
if (delete($sql)) {
    logAktivitas($_SESSION['user_id'], 'Hapus Barang', "Menghapus barang: {$barang['nama_barang']}");
    // ...
}

?>