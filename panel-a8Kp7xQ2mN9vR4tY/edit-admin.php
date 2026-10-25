
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_superadmin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('ID akun tidak valid.');
}

$stmt = $conn->prepare(
    "SELECT id, name, username, role FROM admins WHERE id = ? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account || $account['role'] !== 'admin') {
    http_response_code(403);
    exit('Akun tidak ditemukan atau tidak boleh diubah.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string)($_POST['name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $error = 'Nama harus 3–100 karakter.';
    } elseif (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{7,39}$/', $username)) {
        $error = 'Username harus 8–40 karakter, diawali huruf, dan hanya berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    } elseif ($password !== '' && (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password)
    )) {
        $error = 'Password baru minimal 8 karakter dan harus berisi huruf besar, huruf kecil, angka, serta simbol.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        try {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare(
                    "UPDATE admins SET name = ?, username = ?, password_hash = ?
                     WHERE id = ? AND role = 'admin'"
                );
                $stmt->bind_param('sssi', $name, $username, $hash, $id);
            } else {
                $stmt = $conn->prepare(
                    "UPDATE admins SET name = ?, username = ?
                     WHERE id = ? AND role = 'admin'"
                );
                $stmt->bind_param('ssi', $name, $username, $id);
            }

            $stmt->execute();
            $stmt->close();

            $_SESSION['flash'] = 'Data akun admin berhasil diperbarui.';
            redirect('akun-admin.php');
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                $error = 'Username sudah digunakan akun lain.';
            } else {
                error_log('Gagal mengubah akun admin: ' . $e->getMessage());
                $error = 'Akun gagal diperbarui. Silakan coba kembali.';
            }
        }
    }

    // Pertahankan input nama dan username saat validasi gagal.
    $account['name'] = $name;
    $account['username'] = $username;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Akun Admin — UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
    <main class="admin-main" style="margin-left:0;width:100%">
        <header class="admin-topbar">
            <div>
                <span class="section-kicker">AKSES KHUSUS / AKUN UTAMA</span>
                <h1>Edit akun admin</h1>
            </div>
            <a class="btn btn-outline" href="akun-admin.php">&larr; Kembali</a>
        </header>

        <div class="admin-content">
            <section class="editor-panel" style="max-width:760px">
                <div class="panel-heading">
                    <div>
                        <h2>Perbarui data akun</h2>
                        <p>Kosongkan kolom password jika tidak ingin menggantinya.</p>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" class="form-stack">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <label>
                        Nama lengkap
                        <input name="name" maxlength="100" value="<?= e($account['name']) ?>" required>
                    </label>

                    <label>
                        Username
                        <input name="username" minlength="8" maxlength="40"
                               pattern="[A-Za-z][A-Za-z0-9._-]{7,39}"
                               value="<?= e($account['username']) ?>" required>
                    </label>

                    <label>
                        Password baru (opsional)
                        <input type="password" name="password" minlength="8" autocomplete="new-password">
                        <small class="field-help">Jika diisi, minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol.</small>
                    </label>

                    <label>
                        Konfirmasi password baru
                        <input type="password" name="confirm_password" minlength="8" autocomplete="new-password">
                    </label>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Simpan perubahan</button>
                        <a class="btn btn-outline" href="akun-admin.php">Batal</a>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
