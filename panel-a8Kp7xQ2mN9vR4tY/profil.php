
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_admin();

$id = (int) $_SESSION['admin_id'];
$error = '';
$success = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

$stmt = $conn->prepare(
    "SELECT id, name, username, password_hash, role, profile_image
     FROM admins WHERE id = ? LIMIT 1"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    http_response_code(403);
    exit('Akun tidak ditemukan. Silakan login kembali.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim((string)($_POST['name'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $oldPassword = (string)($_POST['old_password'] ?? '');
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $newImage = $account['profile_image'];
    $uploadedPath = null;

    if (mb_strlen($name) < 3 || mb_strlen($name) > 100) {
        $error = 'Nama harus terdiri dari 3–100 karakter.';
    } elseif (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{7,39}$/', $username)) {
        $error = 'Username harus 8–40 karakter dan diawali huruf.';
    } elseif ($newPassword !== '' && !password_verify(
        $oldPassword,
        $account['password_hash']
    )) {
        $error = 'Password lama tidak sesuai.';
    } elseif ($newPassword !== '' && (
        strlen($newPassword) < 8 ||
        !preg_match('/[A-Z]/', $newPassword) ||
        !preg_match('/[a-z]/', $newPassword) ||
        !preg_match('/[0-9]/', $newPassword) ||
        !preg_match('/[^A-Za-z0-9]/', $newPassword)
    )) {
        $error = 'Password baru minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan simbol.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password baru tidak sama.';
    } else {
        try {
            if (
                isset($_FILES['photo']) &&
                $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException('Foto gagal diunggah.');
                }

                if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                    throw new RuntimeException('Ukuran foto maksimal 2 MB.');
                }

                $tmp = $_FILES['photo']['tmp_name'];
                $imageInfo = @getimagesize($tmp);
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                ];

                if (
                    !$imageInfo ||
                    !isset($allowed[$mime])
                ) {
                    throw new RuntimeException(
                        'Foto harus berupa JPG, PNG, atau WEBP yang valid.'
                    );
                }

                $directory = __DIR__ . '/../uploads/profiles/';

                if (
                    !is_dir($directory) &&
                    !mkdir($directory, 0755, true) &&
                    !is_dir($directory)
                ) {
                    throw new RuntimeException(
                        'Folder penyimpanan foto tidak dapat dibuat.'
                    );
                }

                $filename = bin2hex(random_bytes(16))
                    . '.' . $allowed[$mime];

                $destination = $directory . $filename;

                if (!move_uploaded_file($tmp, $destination)) {
                    throw new RuntimeException(
                        'Foto gagal disimpan. Periksa izin folder uploads.'
                    );
                }

                $newImage = $filename;
                $uploadedPath = $destination;
            }

            $passwordHash = $account['password_hash'];

            if ($newPassword !== '') {
                $passwordHash = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );
            }

            $stmt = $conn->prepare(
                "UPDATE admins
                 SET name = ?, username = ?, password_hash = ?,
                     profile_image = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                'ssssi',
                $name,
                $username,
                $passwordHash,
                $newImage,
                $id
            );
            $stmt->execute();
            $stmt->close();

            if (
                $account['profile_image'] &&
                $newImage !== $account['profile_image']
            ) {
                $oldPath = __DIR__ . '/../uploads/profiles/'
                    . basename($account['profile_image']);

                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $_SESSION['admin_name'] = $name;
            $_SESSION['flash'] = 'Profil berhasil diperbarui.';

            redirect('profil.php');
        } catch (mysqli_sql_exception $e) {
            if ($uploadedPath && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            if ((int)$e->getCode() === 1062) {
                $error = 'Username sudah digunakan akun lain.';
            } else {
                error_log('Gagal memperbarui profil: ' . $e->getMessage());
                $error = 'Profil gagal diperbarui. Silakan coba kembali.';
            }
        } catch (RuntimeException $e) {
            if ($uploadedPath && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya — Ruang Mahasiswa UPY</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
    <main class="admin-main" style="margin-left:0;width:100%">
        <header class="admin-topbar">
            <div>
                <span class="section-kicker">PENGATURAN AKUN</span>
                <h1>Profil Saya</h1>
            </div>
            <a class="btn btn-outline" href="dashboard.php">
                ← Kembali ke Dashboard
            </a>
        </header>

        <div class="admin-content">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <section class="editor-panel" style="max-width:720px">
                <div class="panel-heading">
                    <div>
                        <h2>Pengaturan profil</h2>
                        <p>Perubahan hanya berlaku untuk akun yang sedang login.</p>
                    </div>
                </div>

                <form method="post" enctype="multipart/form-data"
                      class="form-stack">
                    <input type="hidden" name="csrf_token"
                           value="<?= e(csrf_token()) ?>">

                    <div style="text-align:center;margin:10px 0 20px">
                        <?php if ($account['profile_image']): ?>
                            <img
                                src="../uploads/profiles/<?= rawurlencode(basename($account['profile_image'])) ?>"
                                alt="Foto profil"
                                style="width:110px;height:110px;border-radius:50%;object-fit:cover"
                            >
                        <?php else: ?>
                            <div style="width:110px;height:110px;border-radius:50%;margin:auto;display:grid;place-items:center;background:#e9eaf2;font-size:36px;font-weight:700">
                                <?= e(strtoupper(substr($account['name'], 0, 1))) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <label>
                        Foto profil
                        <input type="file" name="photo"
                               accept=".jpg,.jpeg,.png,.webp">
                        <small class="field-help">
                            Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                        </small>
                    </label>

                    <label>
                        Nama lengkap
                        <input type="text" name="name"
                               minlength="3" maxlength="100"
                               value="<?= e($account['name']) ?>" required>
                    </label>

                    <label>
                        Username
                        <input type="text" name="username"
                               minlength="8" maxlength="40"
                               pattern="[A-Za-z][A-Za-z0-9._-]{7,39}"
                               value="<?= e($account['username']) ?>" required>
                    </label>

                    <hr>

                    <h3>Ganti password (opsional)</h3>
                    <p class="field-help">
                        Kosongkan bagian password jika tidak ingin menggantinya.
                    </p>

                    <label>
                        Password lama
                        <input type="password" name="old_password"
                               autocomplete="current-password">
                    </label>

                    <label>
                        Password baru
                        <input type="password" name="new_password"
                               minlength="8" autocomplete="new-password">
                    </label>

                    <label>
                        Konfirmasi password baru
                        <input type="password" name="confirm_password"
                               minlength="8" autocomplete="new-password">
                    </label>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
