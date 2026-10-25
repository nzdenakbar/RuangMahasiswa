<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tentang — Ruang Mahasiswa UPY</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="index.php">
                <img class="brand-logo" src="assets/img/logoupy.webp" alt="Logo Universitas PGRI Yogyakarta">
                <span>ruang<span class="brand-accent">mahasiswa</span><small>UNIVERSITAS PGRI YOGYAKARTA</small></span>
            </a>
            <button class="menu-toggle" aria-label="Buka navigasi" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="main-nav">
                <a href="index.php">Beranda</a>
                <a href="berita.php">Informasi</a>
                <a class="active" href="profil.php">Tentang</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-hero">
            <div class="container">
                <div class="section-kicker">TENTANG PORTAL</div>
                <h1>Satu ruang,<br><em>banyak gerakan.</em></h1>
                <p>Ruang Mahasiswa UPY adalah konsep portal informasi untuk membantu mahasiswa mengakses kabar dan kegiatan lembaga kemahasiswaan dalam satu tempat.</p>
            </div>
        </section>

        <section class="section about-section">
            <div class="container about-grid">

                <!-- 01 TENTANG PORTAL -->
                <div class="about-number">01 <span>— TENTANG PORTAL</span></div>
                <div>
                    <h2>Informasi yang terbuka.<br><span>Kolaborasi yang hidup.</span></h2>
                    <p>Portal ini dirancang sebagai pusat informasi digital yang menyajikan berita, pengumuman, agenda, dokumentasi, dan artikel kegiatan kemahasiswaan. Tujuannya adalah memudahkan penyebaran informasi serta memperkuat koneksi antarmahasiswa dan organisasi.</p>
                </div>

                <!-- 02 VISI & MISI -->
                <div class="about-number">02 <span>— VISI &amp; MISI</span></div>
                <div>
                    <h2>Visi &amp; Misi<br><span>Universitas PGRI Yogyakarta</span></h2>

                    <h3 class="about-subtitle">Visi</h3>
                    <blockquote class="about-vision">
                        Pada tahun 2043 UPY menjadi perguruan tinggi unggul, menghasilkan lulusan yang bertaqwa, profesional, inovatif, memiliki komitmen nasional, dan berwawasan global.
                    </blockquote>

                    <h3 class="about-subtitle">Misi</h3>
                    <ol class="about-list">
                        <li>
                            Mengembangkan tata kelola kelembagaan yang efektif, efisien, demokratis, transparan, dan akuntabel untuk mewujudkan organisasi yang sehat, otonom, dan mempunyai daya saing yang tinggi.
                        </li>
                        <li>
                            Mengoptimalkan pendayagunaan sumber daya yang dimiliki untuk mendukung:
                            <ol class="about-sublist">
                                <li>penyelenggaraan pendidikan akademik dan/atau profesional agar menghasilkan lulusan yang bertaqwa, profesional, inovatif, memiliki komitmen nasional dan berwawasan global;</li>
                                <li>penyelenggaraan kegiatan penelitian untuk mengembangkan ilmu pengetahuan, teknologi, dan seni;</li>
                                <li>penyelenggaraan kegiatan pada masyarakat untuk mewujudkan kesejahteraan masyarakat, bangsa, negara, dan umat manusia.</li>
                            </ol>
                        </li>
                    </ol>
                </div>

                <!-- 03 TUJUAN -->
                <div class="about-number">03 <span>— TUJUAN UPY</span></div>
                <div>
                    <h2>Tujuan<br><span>Universitas PGRI Yogyakarta</span></h2>
                    <p>Menjadikan UPY sebagai perguruan tinggi unggul melalui:</p>

                    <ol class="about-list">
                        <li>Tata kelola universitas yang efektif dan efisien untuk mewujudkan perguruan tinggi yang unggul.</li>
                        <li>Tata kelola universitas yang kredibel, transparan, akuntabel, bertanggung jawab, dan adil untuk mewujudkan perguruan tinggi yang unggul.</li>
                        <li>Pendidikan yang berkualitas untuk menghasilkan lulusan yang bertaqwa, profesional, inovatif, memiliki komitmen nasional dan berwawasan global.</li>
                        <li>Produk penelitian yang dapat mengembangkan ilmu pengetahuan, teknologi, dan seni.</li>
                        <li>Produk pengabdian masyarakat yang dapat mewujudkan kesejahteraan masyarakat, bangsa, negara, dan umat manusia.</li>
                        <li>Kerja sama yang dapat mendukung pengembangan pendidikan, penelitian, dan pengabdian secara berkelanjutan.</li>
                    </ol>

                    <a class="btn btn-primary" href="berita.php">Lihat informasi <span>↗</span></a>
                </div>

            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-bottom">
            <span>© <?= date('Y') ?> Ruang Mahasiswa UPY</span>
            <span>Informasi mahasiswa, gerakan bersama.</span>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>