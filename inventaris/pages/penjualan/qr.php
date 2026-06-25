<?php
ob_start();
require_once '../../config/database.php';
require_once '../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    ob_end_flush();
    exit();
}

$transaksi = getOne("
    SELECT t.*, u.nama_lengkap 
    FROM transaksi t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.id = $id
");

if (!$transaksi) {
    header("Location: index.php");
    ob_end_flush();
    exit();
}

$qr_data = getOne("SELECT * FROM qr_payment WHERE transaksi_id = $id AND status = 'active'");

$expired = strtotime($qr_data['expired_at'] ?? date('Y-m-d H:i:s'));
$now = time();
$remaining = $expired - $now;
$minutes = floor($remaining / 60);
$seconds = $remaining % 60;

if ($remaining <= 0 && $qr_data) {
    update("UPDATE qr_payment SET status = 'expired' WHERE transaksi_id = $id");
}

$qr_string = $qr_data['qr_string'] ?? '';

function generateQRCode($data, $size = 200) {
    return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($data);
}

$qr_image = generateQRCode($qr_string);

$status_label = getStatusLabel($transaksi['status'] ?? 'pending');
$status_badge = getStatusBadge($transaksi['status'] ?? 'pending');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-qr-code"></i> QR Code Pembayaran</h4>
    <a href="index.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-6 mx-auto animate-in">
        <div class="card text-center">
            <div class="card-header">
                <i class="bi bi-qr-code"></i> Scan QR Code untuk Pembayaran
            </div>
            <div class="card-body">
                <?php if ($qr_data && $qr_data['status'] == 'expired'): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> QR Code sudah kadaluarsa!
                    </div>
                <?php endif; ?>
                
                <?php if (!$qr_data): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> QR Code tidak ditemukan atau sudah digunakan!
                    </div>
                <?php endif; ?>
                
                <div class="qr-container p-4">
                    <?php if ($qr_data && $qr_data['status'] == 'active'): ?>
                        <img src="<?php echo $qr_image; ?>" alt="QR Code" class="img-fluid" style="max-width: 250px;">
                        <p class="text-muted mt-2 small">Scan QR Code di atas untuk melakukan pembayaran</p>
                    <?php else: ?>
                        <div class="text-muted py-5">
                            <i class="bi bi-x-circle fs-1 d-block mb-2"></i>
                            QR Code tidak aktif atau sudah kadaluarsa
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <h5>Detail Transaksi</h5>
                    <table class="table table-sm text-start">
                        <tr>
                            <th>Kode Transaksi</th>
                            <td><strong><?php echo htmlspecialchars($transaksi['kode_transaksi'] ?? '-'); ?></strong></td>
                        </tr>
                        <tr>
                            <th>Total Pembayaran</th>
                            <td><h4 class="text-primary">Rp <?php echo number_format($transaksi['total_harga'] ?? 0, 0, ',', '.'); ?></h4></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge <?php echo $status_badge; ?>">
                                    <?php echo $status_label; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat oleh</th>
                            <td><?php echo htmlspecialchars($transaksi['nama_lengkap'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Kadaluarsa</th>
                            <td>
                                <?php if ($qr_data && $qr_data['status'] == 'active'): ?>
                                    <span class="text-danger" id="countdown">
                                        <?php echo sprintf('%02d:%02d', max(0, $minutes), max(0, $seconds)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Sudah kadaluarsa</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="mt-3">
                    <?php if (($transaksi['status'] ?? '') == 'pending' && $qr_data && $qr_data['status'] == 'active'): ?>
                        <button class="btn btn-success" onclick="confirmPayment()">
                            <i class="bi bi-check-circle"></i> Konfirmasi Pembayaran
                        </button>
                        <a href="?bayar=<?php echo $id; ?>" class="btn btn-success" id="btnConfirm" style="display:none;"></a>
                    <?php endif; ?>
                    
                    <?php if (($transaksi['status'] ?? '') == 'paid'): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> Pembayaran sudah dikonfirmasi! Status: <strong>Lunas</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($qr_data && $qr_data['status'] == 'active' && $remaining > 0): ?>
let remaining = <?php echo $remaining; ?>;
const countdownElement = document.getElementById('countdown');

function updateCountdown() {
    if (remaining <= 0) {
        countdownElement.textContent = '00:00';
        countdownElement.className = 'text-danger';
        location.reload();
        return;
    }
    
    const minutes = Math.floor(remaining / 60);
    const seconds = remaining % 60;
    countdownElement.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    
    if (remaining < 60) {
        countdownElement.className = 'text-danger fw-bold';
    }
    
    remaining--;
}

setInterval(updateCountdown, 1000);
<?php endif; ?>

function confirmPayment() {
    if (confirm('Apakah pembayaran sudah diterima?')) {
        document.getElementById('btnConfirm').click();
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>