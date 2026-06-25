<?php
require_once '../../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$kategori = getOne("SELECT * FROM kategori WHERE id = $id");

if (!$kategori) {
    header("Location: index.php");
    exit();
}

// Cek apakah kategori digunakan oleh barang
$cek = getOne("SELECT id FROM barang WHERE kategori_id = $id LIMIT 1");

if ($cek) {
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
                <p>Kategori <strong>'{$kategori['nama_kategori']}'</strong> tidak bisa dihapus karena masih digunakan oleh beberapa barang.</p>
                <a href='index.php' class='btn btn-primary'>Kembali</a>
            </div>
        </div>
        <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit();
}

$sql = "DELETE FROM kategori WHERE id = $id";

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
                <p>Kategori <strong>'{$kategori['nama_kategori']}'</strong> berhasil dihapus.</p>
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
?>