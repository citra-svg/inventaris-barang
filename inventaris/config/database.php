<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'inventaris_db';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function query($sql) {
    global $conn;
    return $conn->query($sql);
}

function get($sql) {
    global $conn;
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getOne($sql) {
    global $conn;
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}

function insert($sql) {
    global $conn;
    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    return false;
}

function update($sql) {
    global $conn;
    return $conn->query($sql);
}

function delete($sql) {
    global $conn;
    return $conn->query($sql);
}

// ============= FUNGSI LOG AKTIVITAS =============
function logAktivitas($user_id, $aktivitas, $detail = null) {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $sql = "INSERT INTO log_aktivitas (user_id, aktivitas, detail, ip_address, user_agent) 
            VALUES ('$user_id', '$aktivitas', '$detail', '$ip', '$user_agent')";
    return $conn->query($sql);
}

// ============= FUNGSI CEK STOK MENIPIS =============
function getStokMenipis() {
    return get("SELECT COUNT(*) as total FROM barang WHERE stok <= min_stok AND stok > 0");
}

function getStokHabis() {
    return get("SELECT COUNT(*) as total FROM barang WHERE stok = 0");
}

function getTotalNotifikasi() {
    $menipis = getStokMenipis()[0]['total'] ?? 0;
    $habis = getStokHabis()[0]['total'] ?? 0;
    return $menipis + $habis;
}

// ============= FUNGSI LABEL & BADGE =============
function getStatusLabel($status) {
    $labels = [
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'failed' => 'Gagal',
        'expired' => 'Kadaluarsa',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak'
    ];
    return $labels[$status] ?? ucfirst($status);
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-warning',
        'paid' => 'bg-success',
        'failed' => 'bg-danger',
        'expired' => 'bg-secondary',
        'approved' => 'bg-primary',
        'rejected' => 'bg-danger'
    ];
    return $badges[$status] ?? 'bg-secondary';
}

// ============= 🔥 TAMBAHKAN INI! =============
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../dashboard.php');
        exit();
    }
}

function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}
?>