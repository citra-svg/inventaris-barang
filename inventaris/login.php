login
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit();
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        // 🔥 PAKAI MD5, BUKAN PASSWORD_VERIFY!
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = MD5('$password')";
        $result = query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            
            header("Location: pages/dashboard.php");
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventaris</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29 0%, #1a1a2e 30%, #16213e 60%, #0f3460 100%);
            position: relative;
            overflow: hidden;
        }
        .glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
        .glow-1 { width: 400px; height: 400px; top: -200px; right: -100px; background: rgba(102, 126, 234, 0.15); }
        .glow-2 { width: 300px; height: 300px; bottom: -100px; left: -100px; background: rgba(245, 87, 108, 0.1); }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-card {
            position: relative;
            z-index: 1;
            max-width: 420px;
            width: 100%;
            padding: 40px 35px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease-out;
        }
        .login-card .logo-icon { font-size: 56px; color: #667eea; margin-bottom: 10px; display: block; text-shadow: 0 0 40px rgba(102, 126, 234, 0.3); }
        .login-card h3 { color: white; font-weight: 800; font-size: 28px; letter-spacing: -0.5px; margin-bottom: 5px; }
        .login-card .subtitle { color: rgba(255,255,255,0.5); font-size: 14px; margin-bottom: 30px; font-weight: 400; }
        .login-card .form-control {
            border-radius: 12px;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: white;
            font-size: 15px;
            transition: all 0.3s;
        }
        .login-card .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .login-card .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            color: white;
        }
        .login-card .input-group-text {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: rgba(255,255,255,0.4);
        }
        .login-card .input-group .form-control { border-radius: 0 12px 12px 0; }
        .login-card .btn-primary {
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }
        .login-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 40px rgba(102, 126, 234, 0.4);
        }
        .login-card .form-label { color: rgba(255,255,255,0.7); font-weight: 600; font-size: 13px; letter-spacing: 0.3px; }
        .login-card .alert {
            border-radius: 12px;
            background: rgba(245, 87, 108, 0.15);
            border: 1px solid rgba(245, 87, 108, 0.2);
            color: #f5576c;
            backdrop-filter: blur(10px);
        }
        .login-card .alert .btn-close { filter: invert(1); }
        .login-card .footer-text { text-align: center; color: rgba(255,255,255,0.2); font-size: 12px; margin-top: 25px; letter-spacing: 0.5px; }
        .login-card .demo-account { margin-top: 20px; padding: 12px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); }
        .login-card .demo-account p { color: rgba(255,255,255,0.3); font-size: 12px; margin-bottom: 5px; }
        .login-card .demo-account code { color: rgba(255,255,255,0.5); background: rgba(255,255,255,0.05); padding: 2px 10px; border-radius: 6px; font-size: 12px; }
        .login-card .register-link { text-align: center; margin-top: 15px; color: rgba(255,255,255,0.3); }
        .login-card .register-link a { color: #667eea; text-decoration: none; font-weight: 600; }
        .login-card .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="glow glow-1"></div>
    <div class="glow glow-2"></div>
    
    <div class="login-card">
        <div class="text-center">
            <div class="logo-icon"><i class="bi bi-box-seam"></i></div>
            <h3>Sistem Inventaris Barang</h3>
            <p class="subtitle">Silakan login untuk melanjutkan</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i> Login
            </button>
        </form>
        
        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </div>
        
        <div class="demo-account text-center">
            <p>Demo Account:</p>
            <code>admin</code> <span style="color:rgba(255,255,255,0.2);margin:0 5px;">/</span> <code>admin123</code>
        </div>
        
        <div class="footer-text">
            &copy; <?php echo date('Y'); ?> Sistem Inventaris Barang
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>