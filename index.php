<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$featured = $conn->query("SELECT n.*, a.name AS author_name FROM news n JOIN admins a ON a.id=n.author_id WHERE n.status='published' ORDER BY n.published_at DESC LIMIT 1")->fetch_assoc();
$news = $conn->query("SELECT n.*, a.name AS author_name FROM news n JOIN admins a ON a.id=n.author_id WHERE n.status='published' ORDER BY n.published_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
function image_url(?string $image): string {
    return $image ? 'uploads/' . rawurlencode($image) : 'assets/img/news-placeholder.svg';
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal informasi dan berita lembaga kemahasiswaan Universitas PGRI Yogyakarta.">
  <title>Ruang Mahasiswa UPY — Informasi Kemahasiswaan</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container nav-wrap">
    <a class="brand" href="index.php"><img class="brand-logo" src="assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta"><span>ruang<span class="brand-accent">mahasiswa</span><small>UNIVERSITAS PGRI YOGYAKARTA</small></span></a>
    <button class="menu-toggle" aria-label="Buka navigasi" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav class="main-nav">
      <a class="active" href="index.php">Beranda</a><a href="berita.php">Informasi</a><a href="profil.php">Tentang</a>
      
    </nav>
  </div>
</header>
<main>
<section class="hero">
  <div class="hero-grid"></div>
  <div class="container hero-inner">
    <div class="hero-copy">
      <div class="eyebrow"><span class="eyebrow-dot"></span> PUSAT INFORMASI KEMAHASISWAAN</div>
      <h1>Ruang tumbuh,<br><em>ruang bergerak.</em></h1>
      <p>Temukan kabar terbaru, agenda kegiatan, dan cerita inspiratif dari lembaga kemahasiswaan Universitas PGRI Yogyakarta.</p>
      <div class="hero-actions"><a class="btn btn-primary" href="berita.php">Jelajahi informasi <span>↗</span></a><a class="text-link" href="profil.php">Kenali ruang kami <span>→</span></a></div>
      <div class="hero-meta"><span class="meta-icon">✳</span><span>Aktif, kolaboratif, dan berdampak.</span></div>
    </div>
    <div class="hero-art hero-image-wrap">
      <!-- Ganti file hero-kampus.svg dengan gambar pilihanmu, atau ubah src ke assets/img/nama-gambar.jpg -->
      <img class="hero-image" src="assets/img/gambarupy.jpg" alt="Ilustrasi kegiatan mahasiswa di kampus">
    </div>
  </div>
  <div class="hero-bottom container"><span>YOGYAKARTA, INDONESIA</span><span>BERGERAK BERSAMA MAHASISWA <b>↓</b></span></div>
</section>
<section class="section intro-section">
  <div class="container intro-row"><div><div class="section-kicker">01 / RUANG INFORMASI</div><h2>Yang bergerak di kampus,<br><span>dimulai dari sini.</span></h2></div><p>Satu ruang untuk berbagi kabar dan menghubungkan mahasiswa dengan berbagai kegiatan, gagasan, dan kesempatan di lingkungan kampus.</p></div>
  <div class="container category-grid">
    <a href="berita.php?category=Kegiatan" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/kegiatanupy.jpg" alt="Ilustrasi kegiatan mahasiswa"><span class="category-num">01</span></div><div class="category-content"><h3>Kegiatan</h3><p>Program, acara, dan gerakan mahasiswa.</p></div></a>
    <a href="berita.php?category=Pengumuman" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/pengumumanupy.jpg" alt="Ilustrasi pengumuman kampus"><span class="category-num">02</span></div><div class="category-content"><h3>Pengumuman</h3><p>Informasi penting dan jadwal terbaru.</p></div></a>
    <a href="berita.php?category=Prestasi" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/prestasiupy.jpg" alt="Ilustrasi piala prestasi mahasiswa"><span class="category-num">03</span></div><div class="category-content"><h3>Prestasi</h3><p>Cerita pencapaian dan inspirasi.</p></div></a>
    <a href="berita.php?category=Artikel" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/artikel.jpg" alt="Ilustrasi artikel dan gagasan mahasiswa"><span class="category-num">04</span></div><div class="category-content"><h3>Artikel</h3><p>Ide, opini, dan cerita dari mahasiswa.</p></div></a>
    <a href="berita.php?category=Hibah" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/hibahupy.jpg" alt="Ilustrasi artikel dan gagasan mahasiswa"><span class="category-num">05</span></div><div class="category-content"><h3>Hibah</h3><p>Informasi program hibah, pendanaan kegiatan, dan peluang pengembangan kreativitas mahasiswa.</p></div></a>
    <a href="berita.php?category=Beasiswa" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/beasiswaupy.jpg" alt="Ilustrasi artikel dan gagasan mahasiswa"><span class="category-num">06</span></div><div class="category-content"><h3>Beasiswa</h3><p>Informasi peluang beasiswa, persyaratan pendaftaran, dan bantuan biaya pendidikan.</p></div></a>
    <a href="berita.php?category=Organisasi Mahasiswa" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/organisasiupy.jpg" alt="Ilustrasi artikel dan gagasan mahasiswa"><span class="category-num">07</span></div><div class="category-content"><h3>Organisasi Mahasiswa</h3><p>Informasi kegiatan organisasi, program kerja, kepengurusan, dan pengembangan kepimpinan mahasiswa.</p></div></a>
    <a href="berita.php?category=Informasi Umum" class="category-card"><div class="category-image-wrap"><img class="category-image" src="assets/img/informasiumum.jpg" alt="Ilustrasi artikel dan gagasan mahasiswa"><span class="category-num">08</span></div><div class="category-content"><h3>Informasi Umum</h3><p>Berita, dan informasi penting seputar kegiatan kampus.</p></div></a>
  </div>
</section>
<section class="section news-section">
  <div class="container">
    <div class="section-heading"><div><div class="section-kicker">02 / KABAR TERKINI</div><h2>Suara dan cerita <span>kampus.</span></h2></div><a class="text-link" href="berita.php">Lihat semua informasi <span>→</span></a></div>
    <?php if (!$news): ?>
      <div class="empty-state"><div class="empty-symbol">✳</div><h3>Informasi pertama segera hadir.</h3><p>Berita yang diterbitkan admin akan muncul di bagian ini.</p></div>
    <?php else: ?>
      <?php if ($featured): ?>
      <a class="featured-news" href="detail-berita.php?id=<?= (int)$featured['id'] ?>">
        <div class="featured-image"><img src="<?= e(image_url($featured['image'])) ?>" alt="<?= e($featured['title']) ?>"><span class="image-label">SOROTAN</span></div>
        <div class="featured-copy"><div class="news-meta"><span><?= e($featured['category']) ?></span><i></i><time><?= date('d M Y', strtotime($featured['published_at'])) ?></time></div><h3><?= e($featured['title']) ?></h3><p><?= e(excerpt($featured['content'], 210)) ?></p><span class="read-more">Baca informasi <b>↗</b></span></div>
      </a>
      <?php endif; ?>
      <div class="news-grid">
      <?php foreach (array_slice($news, 0, 6) as $item): ?>
        <article class="news-card"><a class="news-image" href="detail-berita.php?id=<?= (int)$item['id'] ?>"><img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>"></a><div class="news-meta"><span><?= e($item['category']) ?></span><i></i><time><?= date('d M Y', strtotime($item['published_at'])) ?></time></div><h3><a href="detail-berita.php?id=<?= (int)$item['id'] ?>"><?= e($item['title']) ?></a></h3><p><?= e(excerpt($item['content'], 115)) ?></p><a class="card-arrow" href="detail-berita.php?id=<?= (int)$item['id'] ?>">Baca selengkapnya <span>↗</span></a></article>
      <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<section class="cta-section"><div class="container cta-inner"><div><div class="section-kicker">03 / TERHUBUNG & BERKOLABORASI</div><h2>Punya cerita yang<br>layak dibagikan?</h2><p>Informasi yang baik membuat komunitas tumbuh bersama.</p></div><a class="btn btn-light" href="profil.php">Tentang portal ini <span>↗</span></a><div class="cta-star">✳</div></div></section>
</main>
<footer class="site-footer"><div class="container footer-main"><a class="brand brand-footer" href="index.php"><img class="brand-logo" src="assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta"><span>ruang<span class="brand-accent">mahasiswa</span><small>UNIVERSITAS PGRI YOGYAKARTA</small></span></a><p>Informasi mahasiswa, gerakan bersama.</p><div class="footer-links"><a href="index.php">Beranda</a><a href="berita.php">Informasi</a><a href="profil.php">Tentang</a></div></div><div class="container footer-bottom"><span>© <?= date('Y') ?> Ruang Mahasiswa UPY</span><span>Dibangun untuk ruang kolaborasi.</span></div></footer>
<script src="assets/js/script.js"></script>
</body></html>
