<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$category = trim($_GET['category'] ?? '');
$q = trim($_GET['q'] ?? '');

$sql = "SELECT n.*, a.name AS author_name 
        FROM news n 
        JOIN admins a ON a.id = n.author_id 
        WHERE n.status = 'published'";

$params = []; 
$types = '';

if ($category !== '') { 
    $sql .= " AND n.category = ?"; 
    $params[] = $category; 
    $types .= 's'; 
}

if ($q !== '') { 
    $sql .= " AND (n.title LIKE ? OR n.content LIKE ?)"; 
    $like = '%' . $q . '%'; 
    $params[] = $like; 
    $params[] = $like; 
    $types .= 'ss'; 
}

$sql .= " ORDER BY n.published_at DESC";
$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute(); 
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function image_url_list(?string $image): string { 
    return $image ? 'uploads/' . rawurlencode($image) : 'assets/img/news-placeholder.svg'; 
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi — Ruang Mahasiswa UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body>

    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="index.php">
                <img class="brand-logo" src="assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
                <span>
                    ruang<span class="brand-accent">mahasiswa</span>
                    <small>UNIVERSITAS PGRI YOGYAKARTA</small>
                </span>
            </a>
            <button class="menu-toggle" aria-label="Buka navigasi" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="main-nav">
                <a href="index.php">Beranda</a>
                <a class="active" href="berita.php">Informasi</a>
                <a href="profil.php">Tentang</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-hero">
            <div class="container">
                <div class="section-kicker">RUANG INFORMASI / ARSIP</div>
                <h1>Kabar <em>kampus.</em></h1>
                <p>Temukan informasi, kegiatan, dan cerita terbaru dari ruang kemahasiswaan UPY.</p>
            </div>
        </section>

        <section class="section listing-section">
            <div class="container">
                <form class="search-bar" method="get">
                    <div class="search-field">
                        <span>⌕</span>
                        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Cari judul atau isi informasi...">
                    </div>
                    <select name="category" aria-label="Filter kategori">
                        <option value="">Semua kategori</option>
                        <?php 
                        $categories = [
                            'Kegiatan', 'Pengumuman', 'Prestasi', 'Artikel', 
                            'Hibah', 'Beasiswa', 'Lembaga Kemahasiswaan', 
                            'Organisasi Mahasiswa', 'Informasi Umum', 'Lainnya'
                        ];
                        foreach ($categories as $cat): 
                        ?>
                            <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary" type="submit">Cari &nearr;</button>
                </form>

                <div class="listing-top">
                    <span><?= count($items) ?> informasi ditemukan</span>
                    <?php if($category || $q): ?>
                        <a href="berita.php">Hapus filter &times;</a>
                    <?php endif; ?>
                </div>

                <?php if(!$items): ?>
                    <div class="empty-state">
                        <div class="empty-symbol">⌕</div>
                        <h3>Belum ada informasi yang cocok.</h3>
                        <p>Coba kata kunci lain atau lihat semua informasi.</p>
                        <a class="btn btn-outline" href="berita.php">Lihat semua</a>
                    </div>
                <?php else: ?>
                    <div class="news-grid listing-grid">
                        <?php foreach($items as $item): ?>
                            <article class="news-card">
                                <a class="news-image" href="detail-berita.php?id=<?= (int)$item['id'] ?>">
                                    <img src="<?= e(image_url_list($item['image'])) ?>" alt="<?= e($item['title']) ?>">
                                </a>
                                <div class="news-meta">
                                    <span><?= e($item['category']) ?></span>
                                    <i></i>
                                    <time><?= date('d M Y', strtotime($item['published_at'])) ?></time>
                                </div>
                                <h3>
                                    <a href="detail-berita.php?id=<?= (int)$item['id'] ?>">
                                        <?= e($item['title']) ?>
                                    </a>
                                </h3>
                                <p><?= e(excerpt($item['content'], 130)) ?></p>
                                <a class="card-arrow" href="detail-berita.php?id=<?= (int)$item['id'] ?>">
                                    Baca selengkapnya <span>&nearr;</span>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-main">
            <a class="brand brand-footer" href="index.php">
                <img class="brand-logo" src="assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
                <span>
                    ruang<span class="brand-accent">mahasiswa</span>
                    <small>UNIVERSITAS PGRI YOGYAKARTA</small>
                </span>
            </a>
            <p>Informasi mahasiswa, gerakan bersama.</p>
            <div class="footer-links">
                <a href="index.php">Beranda</a>
                <a href="berita.php">Informasi</a>
                <a href="profil.php">Tentang</a>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>&copy; <?= date('Y') ?> Ruang Mahasiswa UPY</span>
            <span>Dibangun untuk ruang kolaborasi.</span>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>