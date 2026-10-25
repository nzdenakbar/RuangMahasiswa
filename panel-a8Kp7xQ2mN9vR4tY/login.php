<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

if (is_admin()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $conn->prepare("SELECT id, name, username, password_hash, role FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username); 
    $stmt->execute(); 
    $admin = $stmt->get_result()->fetch_assoc();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id']; 
        $_SESSION['admin_name'] = $admin['name']; 
        $_SESSION['admin_role'] = $admin['role'];
        
        redirect('dashboard.php');
    }
    
    $error = 'Username atau kata sandi tidak sesuai.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin — Ruang Mahasiswa UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <main class="login-layout">
        <section class="login-aside">
            <a class="brand brand-light" href="../index.php">
                <img class="brand-logo" src="../assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
                <span>
                    ruang<span class="brand-accent">mahasiswa</span>
                    <small>UNIVERSITAS PGRI YOGYAKARTA</small>
                </span>
            </a>
            <div class="login-aside-copy">
                <div class="section-kicker">CONTENT MANAGEMENT</div>
                <h1>Setiap cerita<br>punya <em>ruangnya.</em></h1>
                <p>Kelola informasi dan kabar kemahasiswaan dari satu tempat.</p>
            </div>
            <div class="login-aside-foot">RUANG MAHASISWA UPY / ADMIN PORTAL</div>
        </section>

        <section class="login-main">
            <a class="back-link" href="../index.php">&larr; Kembali ke website</a>
            <div class="login-card">
                <div class="login-icon">&nearr;</div>
                <div class="section-kicker">AREA TERBATAS</div>
                <h2>Selamat datang.</h2>
                <p>Masuk untuk mengelola informasi portal.</p>

                <?php if($error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" class="form-stack">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <label>
                        Username
                        <input type="text" name="username" autocomplete="username" placeholder="Masukkan username" required autofocus>
                    </label>
                    
                    <label>
                        Kata sandi
                        <input type="password" name="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                    </label>
                    
                    <button class="btn btn-primary btn-full" type="submit">
                        Masuk ke dashboard <span>&nearr;</span>
                    </button>
                </form>

                <div class="login-note">
                    <strong>Akses terbatas</strong><br>
                    <small>Akun admin dibuat oleh akun utama lembaga kemahasiswaan. Gunakan username unik dan kata sandi yang kuat.</small>
                </div>
            </div>
        </section>
    </main>
</body>
</html>