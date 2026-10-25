<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) { 
    http_response_code(404); 
    exit('Informasi tidak ditemukan.'); 
}

$stmt = $conn->prepare("SELECT n.*, a.name AS author_name FROM news n JOIN admins a ON a.id = n.author_id WHERE n.id = ? AND n.status = 'published' LIMIT 1");
$stmt->bind_param('i', $id); 
$stmt->execute(); 
$item = $stmt->get_result()->fetch_assoc();

if (!$item) { 
    http_response_code(404); 
    exit('Informasi tidak ditemukan.'); 
}

$img = $item['image'] ? 'uploads/' . rawurlencode($item['image']) : 'assets/img/news-placeholder.svg';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($item['title']) ?> — Ruang Mahasiswa UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="index.php">
                <img class="brand-logo" src="assets/img/logo-lembaga.svg" alt="Logo Universitas PGRI Yogyakarta">
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
        <article class="article-page">
            <div class="container article-container">
                <a class="back-link" href="berita.php">&larr; Kembali ke informasi</a>
                
                <div class="article-head">
                    <div class="news-meta">
                        <span><?= e($item['category']) ?></span>
                        <i></i>
                        <time><?= date('d F Y', strtotime($item['published_at'])) ?></time>
                    </div>
                    <h1><?= e($item['title']) ?></h1>
                    <p class="article-byline">Dipublikasikan oleh <?= e($item['author_name']) ?></p>
                </div>
                
                <div class="article-cover">
                    <img src="<?= e($img) ?>" alt="<?= e($item['title']) ?>">
                </div>
                
                <div class="article-content">
                    <?= nl2br(e($item['content'])) ?>
                </div>
                
                <div class="article-end">
                    <span>RUANG MAHASISWA UPY</span>
                    <a href="berita.php">Jelajahi informasi lainnya &nearr;</a>
                </div>
            </div>
        </article>
    </main>

    <footer class="site-footer">
        <div class="container footer-bottom">
            <span>&copy; <?= date('Y') ?> Ruang Mahasiswa UPY</span>
            <span>Informasi mahasiswa, gerakan bersama.</span>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>