<?php
$base_path = '';
$script_path = $_SERVER['SCRIPT_NAME'];
if (strpos($script_path, '/pages/barang/') !== false || 
    strpos($script_path, '/pages/kategori/') !== false ||
    strpos($script_path, '/pages/masuk/') !== false ||
    strpos($script_path, '/pages/keluar/') !== false ||
    strpos($script_path, '/pages/laporan/') !== false ||
    strpos($script_path, '/pages/profil/') !== false ||
    strpos($script_path, '/pages/log/') !== false ||
    strpos($script_path, '/pages/user/') !== false ||
    strpos($script_path, '/pages/penjualan/') !== false) {
    $base_path = '../../';
} elseif (strpos($script_path, '/pages/') !== false) {
    $base_path = '../';
}
?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_path; ?>assets/js/script.js"></script>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>