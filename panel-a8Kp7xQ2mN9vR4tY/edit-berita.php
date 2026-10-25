<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); 
if (!$id) {
    redirect('dashboard.php');
}

$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?"); 
$stmt->bind_param('i', $id); 
$stmt->execute(); 
$item = $stmt->get_result()->fetch_assoc(); 

if (!$item) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'Informasi');
    $content = trim($_POST['content'] ?? '');
    $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    $image = $item['image'];
    
    if ($title === '' || $content === '') {
        $error = 'Judul dan isi informasi wajib diisi.';
    } elseif (mb_strlen($title) > 200) {
        $error = 'Judul maksimal 200 karakter.';
    } else {
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
                    
                    $new = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
                    
                    if (move_uploaded_file($file['tmp_name'], $dir . $new)) {
                        if ($image && is_file($dir . $image)) {
                            unlink($dir . $image);
                        }
                        $image = $new;
                    } else {
                        $error = 'Gambar tidak dapat disimpan.';
                    }
                }
            }
        }
        
        if (!$error) {
            $stmt = $conn->prepare("UPDATE news SET title = ?, category = ?, content = ?, image = ?, status = ?, published_at = IF(? = 'published' AND status = 'draft', NOW(), published_at) WHERE id = ?");
            $stmt->bind_param('ssssssi', $title, $category, $content, $image, $status, $status, $id);
            $stmt->execute();
            
            $_SESSION['flash'] = 'Informasi berhasil diperbarui.';
            redirect('dashboard.php');
        }
    }
    
    $item = array_merge($item, [
        'title' => $title,
        'category' => $category,
        'content' => $content,
        'status' => $status,
        'image' => $image
    ]);
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Informasi — Admin UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        
        <div class="sidebar-label">WORKSPACE</div>
        <a class="sidebar-link active" href="dashboard.php"><span>▦</span> Dashboard</a>
        <a class="sidebar-link" href="tambah-berita.php"><span>＋</span> Tulis informasi</a>
        <a class="sidebar-link" href="../index.php" target="_blank"><span>↗</span> Lihat website</a>
        
        <div class="sidebar-bottom">
            <div class="admin-avatar">
                <?= e(strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1))) ?>
            </div>
            <div class="admin-user">
                <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>
                <small>Administrator</small>
            </div>
            <a class="logout-link" href="logout.php">↗</a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <a class="back-link" href="dashboard.php">&larr; Kembali ke dashboard</a>
                <h1>Edit informasi</h1>
            </div>
        </header>

        <div class="admin-content">
            <div class="editor-layout">
                <section class="editor-panel">
                    <div class="panel-heading">
                        <div>
                            <h2>Perbarui konten</h2>
                            <p>Perubahan akan tersimpan setelah tombol simpan ditekan.</p>
                        </div>
                    </div>

                    <?php if($error): ?>
                        <div class="alert alert-error"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="form-stack editor-form">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        
                        <label>
                            Judul informasi <span class="required">*</span>
                            <input type="text" name="title" maxlength="200" value="<?= e($item['title']) ?>" required>
                        </label>
                        
                        <div class="form-row">
                            <label>
                                Kategori
                                <select name="category">
                                    <?php foreach(['Informasi Umum', 'Kegiatan', 'Pengumuman', 'Prestasi', 'Artikel', 'Hibah', 'Beasiswa', 'Lembaga Kemahasiswaan', 'Organisasi Mahasiswa', 'Lainnya'] as $cat): ?>
                                        <option <?= $item['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            
                            <label>
                                Status publikasi
                                <select name="status">
                                    <option value="published" <?= $item['status'] === 'published' ? 'selected' : '' ?>>Publikasikan</option>
                                    <option value="draft" <?= $item['status'] === 'draft' ? 'selected' : '' ?>>Simpan sebagai draft</option>
                                </select>
                            </label>
                        </div>
                        
                        <label>
                            Isi informasi <span class="required">*</span>
                            <textarea name="content" rows="12" required><?= e($item['content']) ?></textarea>
                        </label>
                        
                        <label>
                            Ganti gambar <span class="optional">(opsional)</span>
                            <div class="upload-box">
                                <input type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp,image/gif">
                                <div class="upload-visual">
                                    <span>&uarr;</span>
                                    <strong>Pilih gambar baru</strong>
                                    <small>JPG, PNG, WEBP atau GIF &middot; Maksimal 5 MB</small>
                                </div>
                                <img id="imagePreview" class="upload-preview" alt="Pratinjau gambar" hidden>
                            </div>
                            <?php if($item['image']): ?>
                                <small class="field-help">Gambar saat ini: <?= e($item['image']) ?></small>
                            <?php endif; ?>
                        </label>
                        
                        <div class="form-actions">
                            <a class="btn btn-outline" href="dashboard.php">Batal</a>
                            <button class="btn btn-primary" type="submit">Simpan perubahan <span>&nearr;</span></button>
                        </div>
                    </form>
                </section>

                <aside class="editor-tips">
                    <div class="tips-icon">✳</div>
                    <h3>Catatan edit</h3>
                    <p>Pastikan perubahan tetap sesuai informasi resmi dan mudah dipahami.</p>
                    <ul>
                        <li>Periksa kembali tanggal dan detail acara.</li>
                        <li>Gambar baru akan menggantikan gambar lama.</li>
                        <li>Draft tidak ditampilkan pada halaman publik.</li>
                    </ul>
                </aside>
            </div>
        </div>
    </main>

    <script src="../assets/js/script.js"></script>
</body>
</html>