<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_admin();

$error = '';

// Foto profil admin yang sedang login (untuk avatar di sidebar)
$adminId = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT profile_image FROM admins WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $adminId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();
$profileImage = $me['profile_image'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Informasi');
    $content = trim($_POST['content'] ?? '');
    $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

    if ($title === '' || $content === '') {
        $error = 'Judul dan isi informasi wajib diisi.';
    } elseif (mb_strlen($title) > 200) {
        $error = 'Judul maksimal 200 karakter.';
    } else {
        $image = null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Gambar gagal diunggah.';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 5 MB.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/gif' => 'gif'
                ];

                if (!isset($allowed[$mime])) {
                    $error = 'Format gambar harus JPG, PNG, WEBP, atau GIF.';
                } else {
                    $dir = __DIR__ . '/../uploads/';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    $image = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

                    if (!move_uploaded_file($file['tmp_name'], $dir . $image)) {
                        $error = 'Gambar tidak dapat disimpan.';
                        $image = null;
                    }
                }
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO news(title, category, content, image, author_id, status, published_at) VALUES(?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('ssssis', $title, $category, $content, $image, $adminId, $status);
            $stmt->execute();

            $_SESSION['flash'] = 'Informasi berhasil disimpan.';
            redirect('dashboard.php');
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tulis Informasi — Admin UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="dashboard-body">

    <aside class="admin-sidebar">
        <a class="brand" href="../index.php">
            <img class="brand-logo" src="../assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
            <span>
                ruang<span class="brand-accent">mahasiswa</span>
                <small>UPY / ADMIN</small>
            </span>
        </a>

        <!-- Tombol garis tiga (hanya tampil di mobile, pojok kanan atas) -->
        <button type="button"
                class="admin-menu-toggle"
                id="adminMenuToggle"
                aria-label="Buka menu navigasi"
                aria-controls="adminNav"
                aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="admin-nav" id="adminNav" aria-label="Navigasi admin">
            <div class="sidebar-label">WORKSPACE</div>

            <a class="sidebar-link" href="dashboard.php"><span>▦</span> Dashboard</a>
            <a class="sidebar-link active" href="tambah-berita.php"><span>＋</span> Tulis informasi</a>

            <?php if (is_superadmin()): ?>
                <!-- Hanya tampil untuk akun utama (superadmin) -->
                <a class="sidebar-link" href="akun-admin.php"><span>♙</span> Kelola akun admin</a>
            <?php endif; ?>

            <a class="sidebar-link" href="profil.php"><span>◉</span> Kelola profil</a>
            <a class="sidebar-link" href="../index.php" target="_blank" rel="noopener"><span>↗</span> Lihat website</a>

            <div class="sidebar-bottom">
                <a class="sidebar-profile" href="profil.php" title="Kelola profil">
                    <div class="admin-avatar">
                        <?php if ($profileImage): ?>
                            <img src="../uploads/profiles/<?= rawurlencode(basename($profileImage)) ?>" alt="Foto profil">
                        <?php else: ?>
                            <?= e(strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="admin-user">
                        <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>
                        <small><?= is_superadmin() ? 'Akun utama' : 'Administrator' ?></small>
                    </div>
                </a>

                <a class="btn-logout" href="logout.php">Keluar</a>
            </div>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <a class="back-link" href="dashboard.php">&larr; Kembali ke dashboard</a>
                <h1>Tulis informasi</h1>
            </div>
        </header>

        <div class="admin-content">
            <div class="editor-layout">
                <section class="editor-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Konten informasi</h2>
                            <p>Isi detail informasi yang akan dibagikan.</p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="form-stack editor-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                        <label>
                            Judul informasi <span class="required">*</span>
                            <input type="text" name="title" maxlength="200" value="<?= e($_POST['title'] ?? '') ?>" placeholder="Contoh: Pendaftaran Relawan Festival Kampus 2026" required>
                        </label>

                        <div class="form-row">
                            <label>
                                Kategori
                                <select name="category">
                                    <?php foreach (['Informasi Umum', 'Kegiatan', 'Pengumuman', 'Prestasi', 'Artikel', 'Hibah', 'Beasiswa', 'Lembaga Kemahasiswaan', 'Organisasi Mahasiswa', 'Lainnya'] as $cat): ?>
                                        <option <?= ($_POST['category'] ?? 'Informasi') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label>
                                Status publikasi
                                <select name="status">
                                    <option value="published" <?= ($_POST['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Publikasikan sekarang</option>
                                    <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Simpan sebagai draft</option>
                                </select>
                            </label>
                        </div>

                        <label>
                            Isi informasi <span class="required">*</span>
                            <textarea name="content" rows="12" placeholder="Tulis informasi secara lengkap di sini..." required><?= e($_POST['content'] ?? '') ?></textarea>
                            <small class="field-help">Gunakan paragraf yang jelas dan sertakan detail penting seperti waktu, lokasi, dan kontak jika diperlukan.</small>
                        </label>

                        <label>
                            Gambar / poster <span class="optional">(opsional)</span>
                            <div class="upload-box">
                                <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp,image/gif">
                                <div class="upload-visual">
                                    <span>&uarr;</span>
                                    <strong>Pilih gambar untuk diunggah</strong>
                                    <small>JPG, PNG, WEBP atau GIF &middot; Maksimal 5 MB</small>
                                </div>
                                <img id="imagePreview" class="upload-preview" alt="Pratinjau gambar" hidden>
                            </div>
                        </label>

                        <div class="form-actions">
                            <a class="btn btn-outline" href="dashboard.php">Batal</a>
                            <button class="btn btn-primary" type="submit">Simpan informasi <span>&nearr;</span></button>
                        </div>
                    </form>
                </section>

                <aside class="editor-tips">
                    <h3>Tips publikasi</h3>
                    <p>Pastikan informasi akurat, mudah dipahami, dan sesuai kebutuhan mahasiswa.</p>
                    <ul>
                        <li>Gunakan judul yang ringkas dan jelas.</li>
                        <li>Pilih poster yang tajam dan relevan.</li>
                        <li>Periksa ejaan sebelum menerbitkan.</li>
                        <li>Jangan unggah data pribadi tanpa izin.</li>
                    </ul>
                    <div class="tips-note">Gambar akan disimpan di folder <code>uploads/</code>.</div>
                </aside>
            </div>
        </div>
    </main>

    <script>
    (function () {
        var toggle = document.getElementById('adminMenuToggle');
        var nav = document.getElementById('adminNav');
        if (!toggle || !nav) return;

        function setOpen(open) {
            nav.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            setOpen(!nav.classList.contains('is-open'));
        });

        // Klik di luar menu menutupnya
        document.addEventListener('click', function (e) {
            if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) {
                setOpen(false);
            }
        });

        // Tombol Esc menutup menu
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') setOpen(false);
        });

        // Klik salah satu menu menutup panel
        nav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () { setOpen(false); });
        });

        // Kembali ke layar lebar: pastikan panel tertutup
        var mq = window.matchMedia('(min-width: 761px)');
        var onChange = function (e) { if (e.matches) setOpen(false); };
        if (mq.addEventListener) { mq.addEventListener('change', onChange); }
        else if (mq.addListener) { mq.addListener(onChange); }
    })();
    </script>
    <script src="../assets/js/script.js"></script>
</body>
</html>