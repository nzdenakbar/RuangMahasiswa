
<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Periksa apakah akun superadmin sudah dibuat.
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM admins WHERE role = 'superadmin'"
);

$count = (int) $result->fetch_assoc()['total'];

if ($count > 0) {
    http_response_code(410);
    exit(
        'Inisialisasi akun utama sudah ditutup. ' .
        'Silakan login melalui halaman login admin.'
    );
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string) ($_POST['name'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $error = 'Nama harus terdiri dari 3–100 karakter.';

    } elseif (
        !preg_match('/^[A-Za-z][A-Za-z0-9._-]{7,39}$/', $username)
    ) {
        $error = 'Username harus 8–40 karakter, diawali huruf, dan hanya berisi huruf, angka, titik, garis bawah, atau tanda hubung.';

    } elseif (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    ) {
        $error = 'Password minimal 8 karakter dan harus berisi huruf besar, huruf kecil, angka, serta simbol.';

    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';

    } else {
        try {
            // Hash password sebelum disimpan.
            $hash = password_hash($password, PASSWORD_DEFAULT);

            /*
             * Transaksi dan penguncian tabel membantu mencegah
             * dua permintaan setup bersamaan membuat superadmin.
             */
            $conn->begin_transaction();

            $conn->query("LOCK TABLES admins WRITE");

            $check = $conn->query(
                "SELECT COUNT(*) AS total
                 FROM admins
                 WHERE role = 'superadmin'"
            );

            $existing = (int) $check->fetch_assoc()['total'];

            if ($existing > 0) {
                $conn->query("UNLOCK TABLES");
                $conn->rollback();

                http_response_code(410);
                exit('Akun utama sudah dibuat. Silakan login.');
            }

            $stmt = $conn->prepare(
                "INSERT INTO admins
                    (name, username, password_hash, role)
                 VALUES (?, ?, ?, 'superadmin')"
            );

            $stmt->bind_param('sss', $name, $username, $hash);
            $stmt->execute();
            $stmt->close();

            $conn->query("UNLOCK TABLES");
            $conn->commit();

            $_SESSION['flash'] =
                'Akun utama berhasil dibuat. Silakan login menggunakan username dan password yang baru dibuat.';

            header(
                'Location: panel-a8Kp7xQ2mN9vR4tY/login.php',
                true,
                303
            );
            exit;

        } catch (Throwable $e) {
            try {
                $conn->query("UNLOCK TABLES");
                $conn->rollback();
            } catch (Throwable $ignored) {
                // Abaikan kegagalan pembersihan transaksi.
            }

            error_log('Setup superadmin gagal: ' . $e->getMessage());

            if (
                $e instanceof mysqli_sql_exception &&
                (int) $e->getCode() === 1062
            ) {
                $error = 'Username sudah digunakan. Silakan pilih username lain.';
            } else {
                $error = 'Akun gagal dibuat. Periksa database dan coba lagi.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inisialisasi Akun Utama — UPY</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-body">
    <main class="login-layout">
        <section class="login-aside">
            <div class="login-aside-copy">
                <div class="section-kicker">SETUP SATU KALI</div>
                <h1>Akun utama<br><em>lembaga.</em></h1>
                <p>Buat akun pemilik utama. Akun ini satu-satunya yang berhak membuat akun admin lain.</p>
            </div>
            <div class="login-aside-foot">RUANG MAHASISWA UPY / INITIAL SETUP</div>
        </section>

        <section class="login-main">
            <div class="login-card">
                <div class="section-kicker">INISIALISASI TERKUNCI</div>
                <h2>Buat akun utama</h2>
                <p>Halaman ini otomatis tertutup setelah akun utama berhasil dibuat.</p>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" class="form-stack" autocomplete="off">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrf_token()) ?>"
                    >

                    <label>
                        Nama penanggung jawab
                        <input
                            name="name"
                            maxlength="100"
                            minlength="3"
                            required
                            value="<?= e($name ?? '') ?>"
                        >
                    </label>

                    <label>
                        Username utama
                        <input
                            name="username"
                            minlength="8"
                            maxlength="40"
                            pattern="[A-Za-z][A-Za-z0-9._-]{7,39}"
                            required
                            autocomplete="username"
                            value="<?= e($username ?? '') ?>"
                        >
                        <small class="field-help">8–40 karakter; jangan gunakan kata “admin” saja.</small>
                    </label>

                    <label>
                        Password utama
                        <input
                            type="password"
                            name="password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >
                        <small class="field-help">Minimal 8 karakter: huruf besar/kecil, angka, serta simbol.</small>
                    </label>

                    <label>
                        Ulangi password
                        <input
                            type="password"
                            name="confirm_password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                        >
                    </label>

                    <button class="btn btn-primary btn-full" type="submit">
                        Buat akun utama &nearr;
                    </button>
                </form>

                <div class="login-note">
                    <strong>Penting</strong><br>
                    <small>Setelah akun dibuat, hapus atau nonaktifkan file setup-utama.php agar tidak dapat digunakan kembali.</small>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
